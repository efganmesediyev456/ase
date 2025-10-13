<?php

namespace App\Jobs;

use App\Models\PackageCarrier;
use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ResetPackageWeightJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $track;

    public function __construct($track)
    {
        $this->track = $track;
    }

    public function handle()
    {
//        package_carries table (track_id column)
//        1) packages_carriers.status >=1  demeli smartda deklarasiya var.
//        2) package_carriers.ecoM_REGNUMBER not null  demeli smart deklarasiya nomre bizde bazada artiq var. Ve depesh elemek ola
        $track = $this->track;
        $track = Track::query()->find($track->id);
        $packageCarriers = PackageCarrier::query()->where('track_id', $track->id)->first();
        if(!$packageCarriers || !$packageCarriers->status) {
            $old = $track->weight;
            $track->weight = floatval($old) + floatval(rand(100, 400) / 1000);
            $new = $track->weight;
            $track->comment_txt = "Bağlamanın çəkisi Shipment sorğusu zamanı avtomatik artırıldı! ($old => $new)";
            $track->save();
            if($packageCarriers) {
                $track->carrierReset();
            }
        }
    }
}
