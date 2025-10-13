<?php

namespace App\Jobs;

use App\Models\Azerpost\AzerpostOrder;
use App\Models\Azerpost\AzerpostPackage;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Track;
use App\Services\Azerpost\AzerpostService;
use App\Services\Package\PackageService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class HandleAzerpostStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $azPackage;
    private $status;

    public function __construct($azPackage, $status)
    {
        $this->azPackage = $azPackage;
        $this->status = $status;
    }

    public function handle()
    {
        $azPackage = $this->azPackage;
        $_status = $this->status;
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

                    Notification::sendPackage($package->id, Package::STATES['InBaku']);
                }
                if ($track) {
                    $track->comment_txt = "Bağlama(Azerpost) məntəgəyə çatdı.";
                    $track->status = Track::STATES['InBaku'];
                    $track->save();

                    Notification::sendTrack($track->id, Track::STATES['InBaku']);

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
    }
}
