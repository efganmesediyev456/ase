<?php

namespace App\Services\Integration;

use App\Models\Customer;
use App\Models\PackageGood;
use App\Models\RuType;
use App\Models\Track;
use App\Models\TrackStatus;
use App\Models\UnitradePackage;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;

class BaseService
{
    const STATES = [
        'WaitingDomesticShipment' => 0,     // when package created on Unitrade
        'OnTransfer' => 0,     // when package on transfer to Unitrade
        'Received' => 1,     // when Unitrade accept package
        'WaitingForDeclaration' => 1,     // when Unitrade send package to ase
        'SentToCustoms' => 5,
        'ExternalStorage' => 7,     // when User(customer) declared package and ase send  /declarePackage api request
        'WaitingForAirTransport' => 8,     // when Unitrade adds package to box
        'OnWay' => 12,     // when Unitrade send package
        'StoppedInCustoms' => 18,    // when package is undergoing customs clearance procedures.
        'CustomsClearance' => 44,    // when package is undergoing customs clearance procedures.
        'CustomsCompleted' => 25,     //
        'RTO' => 28,     // when  package is returned
        'Sorting' => 20,    // The package sorting in a Carrier's internal storage facility.
        'PudoAccepted' => 24,    // The package is on at Pudo(Carrier's internal storage facility).
        'AtPudo' => 16,    // The package is on at Pudo(Carrier's internal storage facility).
        'OutForDelivery' => 21,     // The package is out for delivery and should reach the destination soon.
        'Delivered' => 17,    // The package has been successfully delivered to its destination.
        'FailedAttempt' => 22,     // The package has been returned for some reason. Waiting for delivery at the Pudo. (courier packages)
        'Undelivered' => 23,    //
        'Deleted' => 4,     // The package information has been deleted from the system.
        'Rejected' => 19,     // The package information has been deleted from the system.
	    'DeliveredByCourier' => 50, // Unitrade Delivered By Courier
	    'InCustomsNeutral' => 46, // Unitrade Delivered By Courier
	    'Overlimit' => 48,
	    'Prohibited' => 49,
    ];

    const WAREHOUSE = [
        'azerpost_offices' => "AZ",
        'azeri_express_offices' => "EX",
        'delivery_points' => "DP",
        'surat_offices' => "SR",
        'yenipoct_offices' => "YP",
    ];

    const WAREHOUSES = [
        'ozon' => 12,
        'iherb' => 16,
        'unitrade' => 12,
        'china_meest' => 16,
        'gfs' => 17,
        'taobao' => 18,
    ];

    const CURRENCIES = [
        'USD' => 0,
        'AZN' => 1,
        'EUR' => 2,
        'TRY' => 3,
        'RUB' => 4,
        'GBP' => 5,
        'CNY' => 6,
        'AED' => 7,
        'KZT' => 8,
    ];

    const PARTNERS = [
        1 => "iHerb",
        2 => "Wildberries",
        3 => "Ozon",
        4 => "MakeUp",
        5 => "CSE RU",
        6 => "ASE Express TR",
        7 => "China Meest",
        8 => "Gfs",
        9 => "Taobao",
    ];

    const PARTNERS_MAP = [
        "IHERB" => 1,
        "WILDBERRIES" => 2,
        "OZON" => 3,
        "MAKEUP" => 4,
        "CSE_RU" => 5,
        "ASE_EXPRESS_TR" => 6,
        "CHINA_MEEST" => 7,
        "GFS" => 8,
        "TAOBAO" => 9,
    ];

    const PARTNER_WEBSITES = [
        "TAOBAO" => 'https://taobao.com',
        "GFS" => 'https://temu.com',
        "OZON" => 'https://ozon.ru',
        "CHINA_MEEST" => 'https://ozon.ru',
        "IHERB" => 'https://iherb.com',
    ];

    public function createCustomer($params)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $params->buyer['phone_number']);

        if (strpos($phoneNumber, '994') === 0 && strlen($phoneNumber) > 12) {
            $phoneNumber = str_replace('994', '', $phoneNumber);
        }

        $password = rand(10000, 99999);

        $buyerName = $params->buyer['first_name'];

        if ($params->buyer['first_name'] === $params->buyer['last_name']) {
            $nameParts = explode(' ', $buyerName, 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';
        } else {
            $firstName = $params->buyer['first_name'];
            $lastName = $params->buyer['last_name'];
        }
        return Customer::query()->create([
            'partner_id' => $params->partner_id ?? 3,
            'name' => $firstName,
            'surname' => $lastName,
            'fullname' => $firstName . ' ' . $lastName,
            'email' => $params->buyer['email_address'],
            'password' => Hash::make($password),
            'address' => $params->buyer['shipping_address'],
            'fin' => $params->buyer['pin_code'],
            'phone' => "994$phoneNumber",
            'city_name' => $params->buyer['city'],
            'region_name' => $params->buyer['city'],
            'zip_code' => $params->buyer['zip_code'],
        ]);
    }

    public function createCourier(Track $track)
    {
        //TODO:: complete this function
    }
}
