<?php

namespace App\Console\Commands;

use App\Models\IntegrationStatusSend;
use App\Models\Package;
use App\Models\Track;
use App\Services\Integration\BaseService;
use App\Services\Integration\EquickService;
use App\Services\Integration\GfsService;
use App\Services\Integration\UnitradeService;
use Artisan;
use Illuminate\Console\Command;

class IntegrationSendStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'integration {--type=ozon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send statuses for integration';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('type') == 'ozon') {
            $this->ozon();
        }
    }

    public function ozon(){
        $service = $this->getServiceByPartnerId(3);
        $ozon_packages = IntegrationStatusSend::where('send',0)->where('partner_id',3)->get();
        foreach($ozon_packages as $ozon_package){
            $track = Track::find($ozon_package->parcel_id);
            $result = $service->updateStatusV3($track, $ozon_package->status);
            $ozon_package->request_body = json_encode($result['request_body']);
            $ozon_package->response = $result['response'];
            $ozon_package->send = $result['success'] ? 1 : 2;
            $ozon_package->save();
        }
    }
    private function getServiceByPartnerId($partnerId)
    {
        switch ($partnerId) {
            case BaseService::PARTNERS_MAP['GFS']:
                return new GfsService();
            case BaseService::PARTNERS_MAP['OZON']:
                return new UnitradeService();
            case BaseService::PARTNERS_MAP['TAOBAO']:
                return new EquickService();
            default:
                return null;
        }
    }

    private function getEntity(string $type, int $packageId)
    {
        return $type === 'package' ? Package::find($packageId) : Track::find($packageId);
    }


}


