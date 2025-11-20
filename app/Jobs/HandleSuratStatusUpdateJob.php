<?php

namespace App\Jobs;

use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Surat\SuratOrder;
use App\Models\Surat\SuratPackage;
use App\Models\Track;
use App\Services\Package\PackageService;
use App\Services\Surat\SuratService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class HandleSuratStatusUpdateJob implements ShouldQueue
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
        $suratPackage = SuratPackage::query()
            ->where('barcode', $tracking)
            ->first();

        if (!$suratPackage) {
            Log::channel('surat')->error('Package not found', ['package' => $tracking]);
            return;
        }

        if ($suratPackage->status === $status) {
            Log::channel('surat')->error('Package status same', ['package' => $tracking, 'status' => $status]);
            return;
        }

        $order = SuratOrder::find($suratPackage->surat_order_id);

        if (!$order) {
            Log::channel('surat')->error('Package Order not found', ['package' => $tracking]);
            return;
        }

        $order->update([
            'status' => SuratOrder::STATUSES['IN_PROCESS']
        ]);

        $entity = $this->getEntity($suratPackage->type, $suratPackage->package_id);
        $dates = [];

        if ($status == SuratService::STATUSES_IDS["WAREHOUSE"]) {
            $dates['accepted_at'] = now();
            $this->updateAcceptedStatus($entity);
        }
        if ($status == SuratService::STATUSES_IDS["ARRIVEDTOPOINT"]) {
            $dates['arrived_at'] = now();
            $this->updateArrivedToPudoStatus($entity);
        }
        if ($status == SuratService::STATUSES_IDS["DELIVERED"]) {
            $dates['arrived_at'] = now();
            $this->updateDeliveredStatus($entity, $order);
        }

        if ($status == 9) {
            $this->updateRejectedStatus($entity);
        }

        $suratPackage->update(
            array_merge([
                'status' => SuratPackage::STATUSES[SuratService::STATUS_MAP[$status]],
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


    public function updateRejectedStatus($entity){
        $comment = "Bağlama(Surat) kargo tərəfindən geri qaytarıldı.";
        $entity->bot_comment = $comment;
        if ($entity instanceof Package) {
            $entity->status = Package::STATES['Rejected'];
        }elseif ($entity instanceof Track) {
            $entity->status = Track::STATES['Rejected'];
        }
        $entity->save();
    }
    private function updateAcceptedStatus($entity)
    {
        (new PackageService())->updateStatus($entity, 20);
        $comment = "Bağlama(Surat) çeşidləmə mərkəzindədir.";
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
        $comment = "Bağlama(Surat) məntəgəyə çatdı.";
        $this->updateEntityStatus($entity, $comment, "InBaku");
        (new PackageService())->updateStatus($entity, 16);
    }

    private function updateDeliveredStatus($entity, $order)
    {
        $comment = "Bağlama(Surat) müştəriyə təhvil verildi.";

        (new PackageService())->updateStatus($entity, 17);
        $this->updateEntityStatus($entity, $comment, 'Done');

        $undeliveredPackagesCount = SuratPackage::query()
            ->where('surat_order_id', $order->id)
            ->whereIn('status', [SuratPackage::STATUSES['IN_PROCESS'], SuratPackage::STATUSES['WAREHOUSE'], SuratPackage::STATUSES['ARRIVEDTOPOINT']])
            ->count();
        if ($undeliveredPackagesCount == 0) {
            $order->update([
                'status' => SuratOrder::STATUSES['DELIVERED']
            ]);
        }
    }
}
