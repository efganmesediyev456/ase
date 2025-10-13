<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\HandleAzerpostStatusUpdateJob;
use App\Models\Azerpost\AzerpostOrder;
use App\Models\Azerpost\AzerpostPackage;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Track;
use App\Services\Azerpost\AzerpostService;
use App\Services\Package\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class AzerpostController extends Controller
{
    public function updateStatus(Request $request)
    {
        if (!$request->header('ApiToken') || $request->header('ApiToken') !== env('AZERPOCT_WEBHOOK_KEY')) {
            return response()->json([
                'status' => false,
                'data' => "Permission denied!"
            ], 403);
        }

        $request->validate([
            'packages' => ['required', 'array'],
            'status_id' => ['required', 'in:1,2,3,4,5,6,7,8']
        ]);

        $_packages = $request->input('packages');
        $_status = $request->input('status_id');

        $azerpostPackages = AzerpostPackage::query()->whereIn('barcode', $_packages)->get();
        $missingTrackings = array_diff($_packages, $azerpostPackages->pluck('barcode')->toArray());

        if (!empty($missingTrackings)) {
            $response = [
                'status' => false,
                'message' => "Some packages not found",
                'not_found' => $missingTrackings,
                'found_count' => $azerpostPackages->count()
            ];
        } else {
            $response = [
                'status' => true,
                'message' => "All packages found",
                'found_count' => $azerpostPackages->count()
            ];
        }

        foreach ($azerpostPackages as $azPackage) {
            $ignore = DB::table('tracks_ignore_list')->where('tracking_code', $azPackage->barcode)->first();
            if($ignore) {
                continue;
            }

            $package = null;
            $track = null;
            if ($azPackage->type == 'package') {
                $package = Package::find($azPackage->package_id);
            }
            if ($azPackage->type == 'track') {
                $track = Track::find($azPackage->package_id);
            }
            if (AzerpostService::STATUSES[$azPackage->status] != AzerpostService::STATUS_MAP[$_status]) {
                $container = $azPackage->container;
                $comment = $azPackage->comment;
                if (AzerpostService::STATUS_MAP[$_status] == "HAS_PROBLEM") {
                    $comment = 'Order cancelled by Azerpost | ' . $comment;
                    if ($package) {
                        $package->bot_comment = "Order cancelled by Azerpost.";
                        $package->save();
                    }
                    if ($track) {
                        $track->comment_txt = "Order cancelled by Azerpost.";
                        $track->save();
                    }
                }

                $dates = [];
                if (AzerpostService::STATUS_MAP[$_status] == "WAREHOUSE" || AzerpostService::STATUS_MAP[$_status] == "IN_PROCESS") {
                    $dates['accepted_at'] = now();
                    if ($package) {
                        $package->bot_comment = "Bağlama(Azerpost) qəbul edildi.";
                        $package->save();
                    }
                    if ($track) {
                        $track->comment_txt = "Bağlama(Azerpost) qəbul edildi.";
                        $track->save();
                    }
                }
                if (AzerpostService::STATUS_MAP[$_status] == "ARRIVEDTOPOINT") {
                    if ($package) {
                        $package->bot_comment = "Bağlama(Azerpost) məntəgəyə çatdı.";
                        $package->status = Package::STATES['InBaku'];
                        $package->save();

                        Notification::sendPackage($package->id, 'PUDO_DELIVERED_STATUS_PACKAGES');
                    }
                    if ($track) {
                        $track->comment_txt = "Bağlama(Azerpost) məntəgəyə çatdı.";
                        $track->status = Track::STATES['InBaku'];
                        $track->save();

                        Notification::sendTrack($track->id, 'PUDO_DELIVERED_STATUS');

                        (new PackageService())->updateStatus($track, 16);
                    }

                    AzerpostOrder::query()->where('id', $container->id)->update([
                        'status' => AzerpostOrder::STATUSES['IN_PROCESS']
                    ]);
                    $dates['arrived_at'] = now();
                }
                if (AzerpostService::STATUS_MAP[$_status] == "DELIVERED") {
                    if ($package) {
                        $package->bot_comment = "Bağlama(Azerpost) müştəriyə təhvil verildi.";
                        $package->status = Package::STATES['Done'];
                        $package->save();
                    }
                    if ($track) {
                        $track->comment_txt = "Bağlama(Azerpost) müştəriyə təhvil verildi.";
                        $track->status = Track::STATES['Done'];
                        $track->save();

                        (new PackageService())->updateStatus($track, 17);
                    }

                    $undeliveredPackagesCount = AzerpostPackage::query()
                        ->where('azerpost_order_id', $container->id)
                        ->whereIn('status', [AzerpostPackage::STATUSES['IN_PROCESS'], AzerpostPackage::STATUSES['WAREHOUSE'], AzerpostPackage::STATUSES['ARRIVEDTOPOINT']])
                        ->count();
                    if ($undeliveredPackagesCount == 0) {
                        AzerpostOrder::query()->where('id', $container->id)->update([
                            'status' => AzerpostOrder::STATUSES['DELIVERED']
                        ]);
                    }
                    $dates['delivered_at'] = now();
                }

                $azPackage->update(
                    array_merge([
                        'status' => AzerpostPackage::STATUSES[AzerpostService::STATUS_MAP[$_status]],
                        'comment' => $comment,
                    ],
                        $dates
                    )
                );

                $order = AzerpostOrder::query()->withCount(['acceptedPackages', 'packages'])->find($azPackage->azerost_order_id);
                if ($order && AzerpostService::STATUS_MAP[$_status] == "WAREHOUSE") {
                    if ($order->accepted_packages_count == $order->packages_count) {
                        $order->update([
                            'status' => AzerpostOrder::STATUSES['ACCEPTED']
                        ]);
                    }
                }
                if ($order && AzerpostService::STATUS_MAP[$_status] == "ARRIVEDTOPOINT") {
                    if ($order->arrived_packages_count == $order->packages_count) {
                        $order->update([
                            'status' => AzerpostOrder::STATUSES['ARRIVEDTOPOINT']
                        ]);
                    }
                }
            }

//            HandleAzerpostStatusUpdateJob::dispatch($azPackage, $_status);
        }

        return response()->json($response);
    }

    public function updateContainerStatus(Request $request)
    {
        $request->validate([
            'container' => ['required'],
            'status' => ['required', 'in:2,4,5']
        ]);
    }
}
