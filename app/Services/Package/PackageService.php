<?php

namespace App\Services\Package;

use App\Services\Integration\EquickService;
use App\Services\Integration\MeestService;
use App\Services\Kargomat\KargomatService;
use App\Services\Surat\SuratService;
use App\Services\Yenipoct\YenipoctService;
use Log;
use App\Models\Package;
use App\Models\Track;
use App\Services\AzeriExpress\AzeriExpressService;
use App\Services\Azerpost\AzerpostService;
use App\Services\Integration\BaseService;
use App\Services\Integration\GfsService;
use App\Services\Integration\UnitradeService;
use App\Services\Precinct\PrecinctService;

class PackageService
{
    /**
     * @param $officeType (azeriexpress, precinct, azerpost)
     * @param $officeId
     * @param $packageType (package, track)
     * @param $packageBarcode
     * @return array
     */
    public function addPackageToContainer($officeType, $officeId, $packageType, $packageBarcode,$return = false)
    {
        Log::channel('integration')->debug('Add Package to Container', [
            'officeType' => $officeType,
            'officeId' => $officeId,
            'packageType' => $packageType,
            'packageBarcode' => $packageBarcode,
        ]);
        $service = $this->getServiceByOfficeType($officeType);
        if ($service === null) {
            return ['status' => false, 'message' => 'Invalid office type'];
        }

        $package = $this->getPackage($packageType, $packageBarcode);
        if ($package === null) {
            return ['status' => false, 'message' => 'Package&Track tapılmadı!'];
        }
        $validationMessage = $service->validatePackage($package, $packageType,$return);
        if ($validationMessage !== null) {
            return ['status' => false, 'message' => $validationMessage];
        }
        $container = $service->getContainer($officeId);
        $service->createPackage($container, $package);

        return [
            'status' => true,
            'message' => sprintf('Successfully added to Container (%s)', $container->name . '-' . now()->format('d-m-Y'))
        ];
    }

    public function addPackageToCourierContainer($officeType, $officeId, $packageBarcode,$return = false)
    {
        Log::channel('integration')->debug('Add Package to Container', [
            'officeType' => $officeType,
            'officeId' => $officeId,
            'packageType' => 'track',
            'packageBarcode' => $packageBarcode,
        ]);

        $service = $this->getServiceByOfficeType($officeType);
        if ($service === null) {
            return ['status' => false, 'message' => 'Invalid office type'];
        }

        $track = $this->getTrack($packageBarcode);
        if ($track === null) {
            return ['status' => false, 'message' => 'Package&Track tapılmadı!'];
        }
        $validationMessage = $service->validatePackage($track, 'track',$return);
        if ($validationMessage !== null) {
            return ['status' => false, 'message' => $validationMessage];
        }
        $container = $service->getCourierContainer($officeId);
        $service->createCourierPackage($container, $track);

        return [
            'status' => true,
            'message' => sprintf('Successfully added to Container (%s)', $container->name . '-' . now()->format('d-m-Y'))
        ];
    }

    private function getServiceByOfficeType($officeType)
    {
        switch ($officeType) {
            case 'azeriexpress':
                return new AzeriExpressService();
            case 'azerpost':
                return new AzerpostService();
            case 'precinct':
                return new PrecinctService();
            case 'surat':
                return new SuratService();
            case 'yenipoct':
                return new YenipoctService();
            case 'kargomat':
                return new KargomatService();
            default:
                return null;
        }
    }

    private function getPackage($packageType, $packageBarcode)
    {
        $package = null;
        if ($packageType == 'package') {
            $package = Package::query()
                ->where('custom_id', $packageBarcode)
                ->first();
        }

        if ($packageType == 'track') {
            $package = Track::query()
                ->where('tracking_code', $packageBarcode)
                ->first();
        }

        return $package;
    }

    private function getTrack($packageBarcode)
    {

        $track = Track::query()
            ->where('tracking_code', $packageBarcode)
            ->first();

        return $track;
    }


    public function returnDelivery($track, $status = null){
        $service = $this->getServiceByPartnerId($track->partner_id, $track);
        $response = $service->updateReturnDelivery($track, $status);
        return $response;
    }

    public function updateStatus($track, $status = null)
    {

        $service = $this->getServiceByPartnerId($track->partner_id, $track);
        if ($service) {
            if($track->id == 337096){
               return $service->updateStatusNew($track,$status);
            }else{
               return $service->updateStatus($track, $status);
            }
        }
        if ($track instanceof Track) {
            Log::channel('integration')->error('Partner service not Found', [
                'type' => 'track',
                'id' => $track->id,
            ]);
        }
        if ($track instanceof Package) {
            Log::channel('integration')->error('Partner service not Found', [
                'type' => 'package',
                'id' => $track->id,
            ]);
        }
    }

    private function getServiceByPartnerId($partnerId, $track = null)
    {

        switch ($partnerId) {
            case BaseService::PARTNERS_MAP['GFS']:
                return new GfsService();
            case BaseService::PARTNERS_MAP['OZON']:
                return new UnitradeService();
            case BaseService::PARTNERS_MAP['TAOBAO']:
                return new EquickService();
            case BaseService::PARTNERS_MAP['CHINA_MEEST']:
                return new MeestService();
            case BaseService::PARTNERS_MAP['IHERB'] and $track and $track->is_meest:
                return new MeestService();
            default:
                return null;
        }
    }
}
