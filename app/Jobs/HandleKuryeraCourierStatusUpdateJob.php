<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\Track;
use App\Services\AzeriExpress\AzeriExpressService;
use App\Services\Integration\GfsService;
use App\Services\Kuryera\KuryeraService;
use App\Services\Package\PackageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class HandleKuryeraCourierStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $tracking;
    private $status;
    private $reason;
    private $parcel;

    public function __construct($tracking, $status, $reason = null)
    {
        $this->status = $status;
        $this->reason = $reason;
        $this->tracking = $tracking;
    }

    public function handle()
    {
        $track = Track::query()->with(['courier_delivery'])->where('tracking_code', $this->tracking)->first();
        $package = Package::query()->with(['courier_delivery'])->where('custom_id', $this->tracking)->first();

        if($track && !$package){
            $this->parcel = $track;
        }

        if(!$track && $package){
            $this->parcel = $package;
        }

        Log::channel('cs')->debug('Courier order Callback: ', [
            'tracking' => $this->tracking,
            'status' => $this->status,
            'reason' => $this->reason,
        ]);
        if($this->parcel) {
            if ($this->status == KuryeraService::STATUSES_IDS['ACCEPTED']) {
                $this->updateAcceptedStatus();
            }
            if ($this->status == KuryeraService::STATUSES_IDS['COURIER_ASSIGNED']) {
                $this->updateCourierAssignedStatus();
            }
            if ($this->status == KuryeraService::STATUSES_IDS['OUT_FOR_DELIVERY']) {
                $this->updateOutForDeliveredStatus();
            }
            if ($this->status == KuryeraService::STATUSES_IDS['DELIVERED']) {
                $this->updateDeliveredStatus();
            }
            if ($this->status == KuryeraService::STATUSES_IDS['NOT_DELIVERED']) {
                $this->updateNotDeliveredStatus();
            }
        }else{
            Log::channel('cs')->error('Courier order Callback (Track||Parcel not found): ', [
                'tracking' => $this->tracking,
                'status' => $this->status,
                'reason' => $this->reason,
            ]);
        }
    }

    protected function updateAcceptedStatus()
    {
        $this->parcel->setComment("Bağlama(Kuryera) qəbul edildi.");
        $this->parcel->save();

        $cd = $this->parcel->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 1;
            $cd->save();
        }
    }

    protected function updateCourierAssignedStatus()
    {
        $this->parcel->setComment("Bağlama(Kuryera) Kuryer təyin edildi.");
        $this->parcel->save();

        $cd = $this->parcel->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 2;
            $cd->save();
        }
    }

    protected function updateOutForDeliveredStatus()
    {
        $this->parcel->setComment("Bağlama(Kuryera) çatdırılma üçün yola çıxdı.");
        $this->parcel->save();

        $cd = $this->parcel->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 3;
            $cd->save();
        }

        if($this->parcel instanceof Track) {
            (new PackageService())->updateStatus($this->parcel, 21);
        }
    }

    protected function updateDeliveredStatus()
    {
        $this->parcel->setComment("Bağlama(Kuryera) Kuryer tərəfindən müştəriyə təhvil verildi.");
        if($this->parcel instanceof Track) {
            $this->parcel->status = Track::STATES['Done'];
        }
        if($this->parcel instanceof Package) {
            $this->parcel->status = Package::STATES['Done'];
        }
        $this->parcel->save();

        $cd = $this->parcel->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 6;
            $cd->save();
        }
        if($this->parcel instanceof Track) {
            (new PackageService())->updateStatus($this->parcel, 17);
        }
    }

    protected function updateNotDeliveredStatus()
    {
        $this->parcel->setComment("Bağlama(Kuryera) Kuryer tərəfindən müştəriyə təhvil verilə bilmədi. Səbəb: " . KuryeraService::REASONS[$this->reason]);
        if($this->parcel instanceof Track) {
            $this->parcel->status = Track::STATES['Undelivered'];
        }
        if($this->parcel instanceof Package) {
            $this->parcel->status = Package::STATES['Rejected'];
        }
        $this->parcel->save();

        $cd = $this->parcel->courier_delivery;
        if ($cd && count(explode(',', $cd->packages_txt)) == 1) {
            $cd->status = 7;
            $cd->not_delivered_status = KuryeraService::REASONS_MAP[$this->reason];
            $cd->save();
        }

        if($this->parcel instanceof Track) {
            $status = KuryeraService::REASONS_MAP[$this->reason];
            (new PackageService())->updateStatus($this->parcel, $status);
        }
    }
}
