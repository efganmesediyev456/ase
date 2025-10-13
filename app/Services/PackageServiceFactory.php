<?php

namespace App\Services;

use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Azerpost\AzerpostPackage;
use App\Services\AzeriExpress\AzeriExpressService;
use App\Services\Azerpost\AzerpostService;

class PackageServiceFactory
{
    public static function create($barcode)
    {
        $azeriexpressQuery = AzeriExpressPackage::query()->where('barcode', $barcode);
        if ($azeriexpressQuery->exists()) {
            return new AzeriExpressService();
        }

        $azerpostQuery = AzerpostPackage::query()->where('barcode', $barcode);
        if ($azerpostQuery->exists()) {
            return new AzerpostService();
        }

        return null;
    }
}
