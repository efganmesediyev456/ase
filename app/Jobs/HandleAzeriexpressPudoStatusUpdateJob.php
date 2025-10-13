<?php

namespace App\Jobs;

use App\Models\AzeriExpress\AzeriExpressOrder;
use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Track;
use App\Services\AzeriExpress\AzeriExpressService;
use App\Services\Package\PackageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class HandleAzeriexpressPudoStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tracking;
    private $status;

    public function __construct($tracking, $status)
    {
        $this->tracking = $tracking;
        $this->status = $status;
    }

    public function handle()
    {
        $tracking = $this->tracking;
        $status = $this->status;
        $azExPackage = AzeriExpressPackage::query()
            ->where('barcode', $tracking)
            ->first();

        if (!$azExPackage) {
            Log::channel('azeriexpress')->error('Package not found', ['package' => $tracking]);
            return;
        }

        if ($azExPackage->status === $status) {
            Log::channel('azeriexpress')->error('Package status same', ['package' => $tracking, 'status' => $status]);
            return;
        }

        $order = AzeriExpressOrder::find($azExPackage->azeri_express_order_id);

        if (!$order) {
            Log::channel('azeriexpress')->error('Package Order not found', ['package' => $tracking]);
            return;
        }

        $order->update([
            'status' => AzeriExpressOrder::STATUSES['IN_PROCESS']
        ]);

        $entity = $this->getEntity($azExPackage->type, $azExPackage->package_id);
        $dates = [];

        if ($status == AzeriExpressService::STATUSES_IDS["WAREHOUSE"]) {
            $dates['accepted_at'] = now();
            $this->updateAcceptedStatus($entity);
        }
        if ($status == AzeriExpressService::STATUSES_IDS["ARRIVEDTOPOINT"]) {
            $dates['arrived_at'] = now();
            $this->updateArrivedToPudoStatus($entity);
        }
        if ($status == AzeriExpressService::STATUSES_IDS["DELIVERED"]) {
            $dates['delivered_at'] = now();
            $this->updateDeliveredStatus($entity, $order);
        }

        $azExPackage->update(
            array_merge([
                'status' => AzeriExpressPackage::STATUSES[AzeriExpressService::STATUS_MAP[$status]],
                'comment' => "",
            ],
                $dates
            )
        );
    }

    private function getEntity(string $type, int $packageId)
    {
        return $type === 'package' ? Package::find($packageId) : Track::find($packageId);
    }

    private function updateAcceptedStatus($entity)
    {
        (new PackageService())->updateStatus($entity, 20);
        $comment = "Bağlama(AzeriExpress) çeşidləmə mərkəzindədir.";
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
                if ($newStatus == 'InBaku'){
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
                if ($newStatus == 'InBaku') {
                    Notification::sendTrack($entity->id, 'PUDO_DELIVERED_STATUS');
                }else{
                    Notification::sendTrack($entity->id, Track::STATES[$newStatus]);
                }
            }
        }
    }

    private function updateArrivedToPudoStatus($entity)
    {
        $comment = "Bağlama(AzeriExpress) məntəgəyə çatdı.";
        $this->updateEntityStatus($entity, $comment, "InBaku");
        (new PackageService())->updateStatus($entity, 16);
    }

    private function updateDeliveredStatus($entity, $order)
    {
        $comment = "Bağlama(Azeriexpress) müştəriyə təhvil verildi.";

        (new PackageService())->updateStatus($entity, 17);
        $this->updateEntityStatus($entity, $comment, 'Done');

        $undeliveredPackagesCount = AzeriExpressPackage::query()
            ->where('azeri_express_order_id', $order->id)
            ->whereIn('status', [AzeriExpressPackage::STATUSES['IN_PROCESS'], AzeriExpressPackage::STATUSES['WAREHOUSE'], AzeriExpressPackage::STATUSES['ARRIVEDTOPOINT']])
            ->count();
        if ($undeliveredPackagesCount == 0) {
            $order->update([
                'status' => AzeriExpressOrder::STATUSES['DELIVERED']
            ]);
        }
    }
}
