<?php

namespace App\Jobs;

use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\CD;
use App\Models\Courier;
use App\Models\Track;
use App\Services\AzeriExpress\CourierService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
use Log;

class ProcessBarcodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private  $row;

    public function __construct( $row)
    {
        $this->row = $row;
    }

    public function handle()
    {
        try {
            $barcode = $this->row['tracking_no'];
            Log::channel('job')->debug("ProcessBarcodesJob worked", $this->row);

            if ($this->checkSentToAzeriexpress($barcode)) {
                $tracks = Track::query()->where('tracking_code', $barcode)->where('status', '!=', 17)->get();
                $service = App::make(CourierService::class);
                $courier = Courier::find(11);
                CD::assignExternalCourier($courier, $tracks, $service);
            }
        } catch (\Exception $e) {
            Log::channel('job')->error('ProcessBarcodesJob failed', ['error' => $e->getMessage()]);
        }
    }

    private function checkSentToAzeriexpress($barcode): bool
    {
        $track = Track::query()->where('tracking_code', $barcode)->where('status', '!=', 17)->first();
        $azexTrack = AzeriExpressPackage::query()->where('barcode', $barcode)->first();
        if ($track && !$azexTrack) {
            CD::newCD($track, 11, 1);
            return true;
        }

        return false;
    }
}
