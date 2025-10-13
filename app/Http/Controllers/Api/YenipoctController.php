<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Track;
use App\Models\YeniPoct\YenipoctOrder;
use App\Models\YeniPoct\YenipoctPackage;
use App\Services\Package\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Milon\Barcode\DNS1D;

class YenipoctController extends Controller
{
   private $token = '571GK9cMae9Ar70428pw1FKths10wrlesxtXmVFPV2m1PSlsJtzSHewAY8VgGcSv';

    const STATUSES_IDS = [
        "WAITING" => 0,
        "SENT" => 1,
        "WAREHOUSE" => 8,
        "ONWAY" => 3,
        "ARRIVEDTOPOINT" => 4,
        "DELIVERED" => 5,
        "NOT_DELIVERED" => 6,
        "" => 7,
//        "COURIER_ASSIGNED" => 8,
    ];

    const STATUS_MAP = [
        8 => "WAREHOUSE",
        3 => "ONWAY",
        4 => "ARRIVEDTOPOINT",
        5 => "DELIVERED",
        6 => "NOT_DELIVERED",
    ];

    public function updateStatus(Request $request)
    {
        $headerToken = $request->header('token');
        if ($headerToken !== $this->token) {
            return response()->json([
                'message' => 'Unauthorized access. Invalid token.',
            ], 401);
        }
        Log::channel('yenipoct')->info("StatusLog: ", [$request->all()]);

        $request->validate([
            'track_number' => ['required'],
            'status' => ['required', 'in:3,4,5,6,8']
        ]);
        $tracking = $request->input('track_number');
        $status = $request->input('status');

        if ($status == 3){
            return response()->json(["message" => "Package status updated successfully", "barcode"=>$tracking, "code" => 200], 200);
        }

        $ignore = DB::table('tracks_ignore_list')->where('tracking_code', $tracking)->first();
        if($ignore) {
            return response()->json(['status' => true, 'data' => []]);
        }
        $ypPackage = YenipoctPackage::where('barcode', $tracking)
            ->first();

        if (!$ypPackage) {
            Log::channel('yenipoct')->error('Package not found', ['package' => $tracking]);
            return response()->json(["message" => "Package not found!", "code" => 422], 422);
        }

//        if ($ypPackage->status === YenipoctPackage::STATUSES[self::STATUS_MAP[$status]]) {
//            Log::channel('yenipoct')->error('Package status same', ['package' => $tracking, 'status' => $status]);
//            return response()->json(["message" => "Package status is same!", "barcode"=>$tracking, "code" => 422], 422);
//        }

        $order = YenipoctOrder::find($ypPackage->yenipoct_order_id);

        if (!$order) {
            Log::channel('yenipoct')->error('Package Order not found', ['package' => $tracking]);
            return response()->json(["message" => "Package Order not found", "barcode"=>$tracking, "code" => 422], 422);
        }

        $order->update([
            'status' => YenipoctOrder::STATUSES['IN_PROCESS']
        ]);

        $entity = $this->getEntity($ypPackage->type, $ypPackage->package_id);
        $dates = [];

        if ($status == self::STATUSES_IDS["WAREHOUSE"]) {
            $dates['accepted_at'] = now();
            $this->updateAcceptedStatus($entity);
        }
        if ($status == self::STATUSES_IDS["ARRIVEDTOPOINT"]) {
            $dates['arrived_at'] = now();
            $this->updateArrivedToPudoStatus($entity);
        }
        if ($status == self::STATUSES_IDS["DELIVERED"]) {
            $dates['delivered_at'] = now();
            $this->updateDeliveredStatus($entity, $order);
        }

        $ypPackage->update(
            array_merge([
                'status' => YenipoctPackage::STATUSES[self::STATUS_MAP[$status]],
                'comment' => "",
            ],
                $dates
            )
        );
        return response()->json(["message" => "Package status updated successfully", "barcode"=>$tracking, "code" => 200], 200);
    }

    private function getEntity(string $type, int $packageId)
    {
        return $type === 'package' ? Package::find($packageId) : Track::find($packageId);
    }

    private function updateAcceptedStatus($entity)
    {
        (new PackageService())->updateStatus($entity, 20);
        $comment = "Bağlama(YeniPoct) çeşidləmə mərkəzindədir.";
        $this->updateEntityStatus($entity, $comment, "");
    }

    private function updateEntityStatus($entity, string $comment, string $newStatus)
    {
        if ($entity instanceof Package) {
            $entity->bot_comment = $comment;
            if ($newStatus) {
                $entity->status = Package::STATES[$newStatus];
            }
            $entity->save();
            if ($newStatus) {
                if ($newStatus == 'InBaku' || Package::STATES[$newStatus] == 2){
                    Notification::sendPackage($entity->id, 'PUDO_DELIVERED_STATUS_PACKAGES');
                }else{
                    Notification::sendPackage($entity->id, Package::STATES[$newStatus]);
                }
            }
        } elseif ($entity instanceof Track) {
            $entity->comment_txt = $comment;
            if ($newStatus) {
                $entity->status = Track::STATES[$newStatus];
            }
            $entity->save();
            if ($newStatus) {
                if ($newStatus == 'InBaku' || Package::STATES[$newStatus] == 2){
                    Notification::sendTrack($entity->id, 'PUDO_DELIVERED_STATUS');
                }else{
                    Notification::sendTrack($entity->id, Track::STATES[$newStatus]);
                }
            }
        }
    }

    private function updateArrivedToPudoStatus($entity)
    {
        $comment = "Bağlama(YeniPoct) məntəgəyə çatdı.";
        $this->updateEntityStatus($entity, $comment, "InBaku");
        (new PackageService())->updateStatus($entity, 16);
    }

    private function updateDeliveredStatus($entity, $order)
    {
        $comment = "Bağlama(YeniPoct) müştəriyə təhvil verildi.";

        (new PackageService())->updateStatus($entity, 17);
        $this->updateEntityStatus($entity, $comment, 'Done');

//        $undeliveredPackagesCount = YenipoctPackage::where('yenipoct_order_id', $order->id)
//            ->whereIn('status', [YenipoctPackage::STATUSES['IN_PROCESS'], YenipoctPackage::STATUSES['WAREHOUSE'], YenipoctPackage::STATUSES['ARRIVEDTOPOINT']])
//            ->count();

        $deliveredPackageCount = YenipoctPackage::where('yenipoct_order_id', $order->id)->where('status',YenipoctPackage::STATUSES['DELIVERED'])->where('deleted_at',null)->count();
        $totalOrderCount = YenipoctPackage::where('yenipoct_order_id', $order->id)->where('deleted_at',null)->count();

        if ($deliveredPackageCount == $totalOrderCount) {
            $order->update([
                'status' => YenipoctOrder::STATUSES['DELIVERED']
            ]);
        }
    }
}
