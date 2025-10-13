<?php

namespace App\Jobs;

use App\Models\CD;
use App\Models\Courier;
use App\Models\Track;
use App\Services\AzeriExpress\CourierService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\App;
use Log;

class SendCourierDeliveriesToAzeriexpressJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $courierDelivery;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($courierDelivery)
    {
        $this->courierDelivery = $courierDelivery;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::channel('job')->debug('courier-deliveries', []);
        $service = App::make(CourierService::class);
        $tracks = Track::where('tracking_code', $this->courierDelivery->packages_txt)->get();
        $courier = Courier::find($this->courierDelivery->courier_id);

        CD::assignAzeriexpressCourier($courier, $tracks, $service);
    }
}
