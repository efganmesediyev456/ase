<?php

namespace App\Jobs;

use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Track;
use App\Services\AzeriExpress\AzeriExpressService;
use App\Services\Integration\GfsService;
use App\Services\Package\PackageService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Log;

class HandleAzeriexpressCourierStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $track;
    protected $status;
    protected $causeId;
    private $tracking;

    public function __construct($tracking, $status, $causeId = null)
    {
        $this->status = $status;
        $this->causeId = $causeId;
        $this->tracking = $tracking;
    }

    public function handle()
    {
        $this->track = Track::query()->with(['courier_delivery'])->where('tracking_code', $this->tracking)->first();

        Log::channel('azeriexpress')->debug('Courier order Callback: ', [
            'tracking' => $this->tracking,
            'status' => $this->status,
            'causeId' => $this->causeId,
        ]);
        if($this->track) {
            if ($this->status == AzeriExpressService::STATUSES_IDS['DELIVERED']) {
                $this->updateDeliveredStatus();
            }
            if ($this->status == AzeriExpressService::STATUSES_IDS['WAREHOUSE']) {
                $this->updateAcceptedStatus();
            }
            if ($this->status == AzeriExpressService::STATUSES_IDS['COURIER_ASSIGNED']) {
                $this->updateCourierAssignedStatus();
            }
            if ($this->status == AzeriExpressService::STATUSES_IDS['ONWAY']) {
                $this->updateOutForDeliveredStatus();
            }
            if ($this->status == AzeriExpressService::STATUSES_IDS['NOT_DELIVERED']) {
                $this->updateNotDeliveredStatus();
            }
        }else{
            Log::channel('azeriexpress')->error('Courier order Callback (Track not found): ', [
                'tracking' => $this->tracking,
                'status' => $this->status,
                'causeId' => $this->causeId,
            ]);
        }
    }

    protected function updateDeliveredStatus()
    {
        $this->track->comment_txt = "Bağlama(Azeriexpress) Kuryer tərəfindən müştəriyə təhvil verildi.";
        $this->track->status = Track::STATES['Done'];
        $this->track->save();

        $azExPackage = AzeriExpressPackage::query()
            ->where('barcode', $this->track->tracking_code)
            ->first();

        $azExPackage->status = 33;
        $azExPackage->save();

        $cd = $this->track->courier_delivery; 
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 6;
            $cd->save();
        }

        (new PackageService())->updateStatus($this->track, 17);
    }

    protected function updateAcceptedStatus()
    {
        $this->track->comment_txt = "Bağlama(Azeriexpress) Kuryer tərəfindən qəbul edildi.";
        $azExPackage = AzeriExpressPackage::query()
            ->where('barcode', $this->track->tracking_code)
            ->first();

        $azExPackage->status = 30;
        $azExPackage->save();
//        $this->track->status = Track::STATES['Done'];
        $this->track->save();
    }

    protected function updateOutForDeliveredStatus()
    {
        $this->track->comment_txt = "Bağlama(Azeriexpress) çatdırılma üçün yola çıxdı.";
        $this->track->save();

        $azExPackage = AzeriExpressPackage::query()
            ->where('barcode', $this->track->tracking_code)
            ->first();

        $azExPackage->status = 31;

        $azExPackage->save();
        $cd = $this->track->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 3;
            $cd->save();
        }

        (new PackageService())->updateStatus($this->track, 21);
    }
    protected function updateCourierAssignedStatus()
    {
        $this->track->comment_txt = "Bağlama(Azeriexpress) Kuryer təyin edildi.";
        $this->track->save();
        $azExPackage = AzeriExpressPackage::query()
            ->where('barcode', $this->track->tracking_code)
            ->first();

        $azExPackage->status = 36;
        $azExPackage->save();
        $cd = $this->track->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 2;
            $cd->save();
        }

        (new PackageService())->updateStatus($this->track, 21);
    }

    protected function updateNotDeliveredStatus()
    {
        $this->track->comment_txt = "Bağlama(Azeriexpress) Kuryer tərəfindən müştəriyə təhvil verilə bilmədi. Səbəb: " . AzeriExpressService::REASONS[$this->causeId];
        $this->track->status = Track::STATES['Undelivered'];
        $this->track->save();

        $azExPackage = AzeriExpressPackage::query()
            ->where('barcode', $this->track->tracking_code)
            ->first();

        $azExPackage->status = 34;
        $azExPackage->save();

        $status = GfsService::REASONS_MAP[$this->causeId];
        (new PackageService())->updateStatus($this->track, $status);
    }
}
