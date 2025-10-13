<?php

namespace App\Jobs;

use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Azerpost\AzerpostPackage;
use App\Services\AzeriExpress\AzeriExpressService;
use App\Services\Azerpost\AzerpostService;
use App\Services\Interfaces\PackageServiceInterface;
use App\Services\PackageServiceFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateCarrierPackagePaymentStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $barcode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($barcode)
    {
        $this->barcode = $barcode;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = PackageServiceFactory::create($this->barcode);
        if ($service instanceof PackageServiceInterface) {
            $service->updateOrderPayment($this->barcode);
        }
    }
}
