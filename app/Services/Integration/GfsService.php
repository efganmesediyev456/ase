<?php

namespace App\Services\Integration;

use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\Customer;
use App\Models\DeliveryPoint;
use App\Models\Filial;
use App\Models\PackageGood;
use App\Models\Request;
use App\Models\RuType;
use App\Models\Surat\SuratOffice;
use App\Models\Track;
use App\Models\TrackStatus;
use Exception;
use Illuminate\Support\Facades\Hash;

class GfsService extends BaseService
{
    const STATE_MAP = [
        'StoppedInCustoms' => ['code' => 'CTC30', 'description' => "Stopped In Customs"],
        'CustomsClearance' => ['code' => 'CTC02', 'description' => "Under Customs Clearance"],
        'CustomsCompleted' => ['code' => 'CTC03', 'description' => "Customs Clearance Completed"],
        'Sorting' => ['code' => 'OTC01', 'description' => "Parcel picked up from customs"],
        'AtPudo' => ['code' => 'TTC05', 'description' => "Ready for Pick Up by Customer"],
        'OutForDelivery' => ['code' => 'TTC04', 'description' => "Out for delivery, delivery logistics go out to deliver packages"],
        'Undelivered' => ['code' => 'RTC30', 'description' => "Parcel refused by customer 	Package delivery failed and is no longer being delivered, ready for return GFS"],
        'Delivered' => ['code' => 'TTC06', 'description' => "Delivered"],
        'DeliveredByCourier' => ['code' => 'TTC06', 'description' => "Delivered"],

        'TTC47' => ['code' => 'TTC47', 'description' => "Rescheduled with Customer(2023-07-24)"],
        'TTC48' => ['code' => 'TTC48', 'description' => "Customer requests to change location"],
        'TTC55' => ['code' => 'TTC55', 'description' => "Address information is incomplete"],
        'TTC37' => ['code' => 'TTC37', 'description' => "No answer from customer"],
        'TTC36' => ['code' => 'TTC36', 'description' => "Package Damaged"],
        'TTC33' => ['code' => 'TTC33', 'description' => "Wrong phone number"],
        'TTC30' => ['code' => 'TTC30', 'description' => "Parcel refused by customer"],
    ];

    const COURIER_STATES = [
        'TTC47' => "Rescheduled with Customer",
        'TTC48' => "Customer requests to change location",
        'TTC55' => "Address information is incomplete",
        'TTC37' => "No answer from customer",
        'TTC36' => "Package Damaged",
        'TTC33' => "Wrong phone number",
        'TTC30' => "Parcel refused by customer",
    ];

    const REASONS_MAP = [
        1 => 'TTC37',
        2 => 'TTC30',
        3 => 'TTC37',
        4 => 'TTC55',
        5 => 'TTC55'
    ];

    const CLIENT_URL = 'https://gfs-gw.gfs-express.com/gli';
    const CLIENT_ID = 'ase_az';
    const CLIENT_SECRET = 'kqqCGmGMNyPWNPSdSyDmrkHnpCJonFFP';
    private $user = "OW20240614152259";

    public function createPackage($params, $user): array
    {
        $statusId = $params->weight ? self::STATES['WaitingForDeclaration'] : self::STATES['WaitingDomesticShipment'];

        $warehouse = $params->pickUpSiteCode;
        $warehousePrefix = substr($warehouse, 0, 2);
        $warehouseId = substr($warehouse, 2);

        if ($params->deliveryType !== "HOME_DELIVERY" && $warehousePrefix === "DP") {
            $_warehouse = DeliveryPoint::query()->where('id', $warehouseId)->first();
        }
        if ($params->deliveryType !== "HOME_DELIVERY" && $warehousePrefix === "AZ") {
            $_warehouse = AzerpostOffice::query()->where('id', $warehouseId)->first();
        }
        if ($params->deliveryType !== "HOME_DELIVERY" && $warehousePrefix === "EX") {
            $_warehouse = AzeriExpressOffice::query()->where('id', $warehouseId)->first();
        }
        if ($params->deliveryType !== "HOME_DELIVERY" && $warehousePrefix === "SR") {
            $_warehouse = SuratOffice::query()->where('id', $warehouseId)->first();
        }

        $warehouseId = $_warehouse->id ?? null;

        $track = Track::create([
            "type" => $params->orderType ?? 520,
            "partner_id" => $params->partner_id,
            "customer_id" => $user->id,

            "warehouse_id" => self::WAREHOUSES['gfs'],
            "city_id" => $_warehouse->city_id ?? null,
            "tracking_code" => $params->waybillNo,
            "status" => $statusId,
            "website" => self::PARTNER_WEBSITES['GFS'],
            "number_items" => count($params->goodsInfos) ?? 1,
            "weight" => floatval($params->weight) / 1000 + floatval(rand(100, 150) / 1000)?? null,

            "paid" => true,
            "delivery_type" => $params->deliveryType == "HOME_DELIVERY" ? "HD" : "PUDO",
            "delivery_price" => rand(1, 10) / 10,//TODO:: should be come from Company api::$params->delivery->price
            "delivery_price_cur" => $params->shippingAmount->currency,
            "shipping_amount" => $params->declareAmount->amount,
            "shipping_amount_cur" => $params->shippingAmount->currency,
            "currency" => $params->declareAmount->currency,

            "fin" => $params->consigneeInfo->identify,
            "fullname" => $params->consigneeInfo->firstName . ' ' . $params->consigneeInfo->lastName,
            "phone" => $params->consigneeInfo->phoneCode . $params->consigneeInfo->cellphone,
            "email" => $params->consigneeInfo->email ?? null,
            "address" => $params->consigneeInfo->streetAddress,
            "city_name" => $params->consigneeInfo->city,
            "region_name" => $params->consigneeInfo->state,
            "zip_code" => $params->pickUpSiteCode,

            "store_status" => $warehousePrefix === "DP" ? $warehouseId : null,
            "azeriexpress_office_id" => $warehousePrefix === "EX" ? $warehouseId : null,
            "azerpost_office_id" => $warehousePrefix === "AZ" ? $warehouseId : null,
            "surat_office_id" => $warehousePrefix === "SR" ? $warehouseId : null,
            "data" => json_encode($params)
        ]);

        foreach ($params->goodsInfos as $product) {
            $ruType = RuType::query()->where('hs_code', $product->hsCode)->first();

            if (!$ruType) {
                $ruType = new RuType();
                $ruType->hs_code = $product->hsCode;
                $ruType->name_ru = $product->enName . ' - ' . $product->enName;
                $ruType->save();
            }

            PackageGood::create([
                'track_id' => $track->id,
                'number_items' => $product->quantity,
                'weight' => $product->weight,
                'ru_type_id' => $ruType->id,
                'shipping_amount' => $product->declareAmount,
                'shipping_amount_cur' => self::CURRENCIES[$product->declareCurrency],
                'country_id' => $track->country_id,
                'warehouse_id' => self::WAREHOUSES['gfs']
            ]);

            $detailedType[] = $product->quantity . " x " . $ruType->name_ru;
        }

        if ($params->deliveryType == "HOME_DELIVERY") {
            $this->createCourier($track);

            $filial=Filial::getMach($track->address,$track->city_name,$track->region_name);
            if ($filial) {
                Filial::setTrackFilial($track,$filial);
            }
        }


        $track->detailed_type = implode("; ", $detailedType);
        $track->save();

        return [
            "outOrderNo" => $params->outOrderNo,
            "orderType" => $track->courier_delivery_id ? "HOME_DELIVERY" : "PUDO_DELIVERY",
            "waybillNo" => $track->tracking_code,
            "orderLabelUrl" => url('/'),
        ];
    }

    public function updatePackage($unitradePackageQuery, $request)
    {
        //update unitrade packages table
        $unitradePackage = $unitradePackageQuery->first();
        $track = Track::query()->where('id', $unitradePackage->track_id)->first();

        //throw an error if not exists
        $unitradePackageQuery->update([
            'weight' => $request->weight,
            'shipping_invoice_price' => $request->shipping_invoice['invoice_price']
        ]);

        $data = [];
        if ($request->weight && $request->weight != 0) {

            if ($track->paid == 1){

                // if track is paid then we should not update the delivery price (zakariya wanted)
                $track->update([
                    'weight' => $request->weight,
//                    'delivery_price' => $request->shipping_invoice['invoice_price'],
                    'status' => self::STATES['WaitingForDeclaration'],
                ]);
            }else{
                $track->update([
                    'weight' => $request->weight,
                    'delivery_price' => $request->shipping_invoice['invoice_price'],
                    'status' => self::STATES['WaitingForDeclaration'],
                ]);
            }


            $unitradePackageQuery->update([
                'status' => self::STATES['WaitingForDeclaration'],
            ]);
        }

        return $this->prepareResponse($unitradePackage);
    }

    public function main()
    {
        $data = '{"trackingDetails":[{"eventCode":"CTC02","eventDesc":"Under Customs Clearance","eventTime":1719224814,"location":"Baku","originTracking":"{\\"ase desc\\":\\"CustomsClearance\\",\\"ase code\\":\\"abc_123\\"}"}],"waybillNo":"GE2406247002005"}';
        return $sign = $this->generateSignAndCt("dwp.serpens.track_push", "1", "OW20240614152259", "1", $data, "cff45d54c13317a6e2965a8ddf899123", "1719329216733");

    }

    function generateSignAndCt($api, $gw_ver, $gw_user_group, $ver, $data, $cipher, $ct = null)
    {
        $milliseconds = (string)$ct != null ? $ct : round(microtime(true) * 1000);

        $string = $gw_ver . $api . $ver . $gw_user_group . $milliseconds . $data . $cipher;
//        $encodedString = mb_convert_encoding($string, 'UTF-8');
        $sign = md5($string);
        return [$sign, $milliseconds];
    }

    public function updateStatus(Track $track, $status = null)
    {
        if ($status) {
            $statusString = array_search((int)$status, self::STATES, true);
            if (array_key_exists($status, self::COURIER_STATES)) {
                $statusString = $status;
            }
        } else {
            $statusString = array_search($track->status, self::STATES, true);
        }
        if ($statusString === false || !array_key_exists($statusString, self::STATE_MAP)) {
            $track->error_txt = "status not found: $status | " . $track->status;
            $track->save();
            return false;
        }
        try {
            $trackStatus = TrackStatus::query()->create([
                'track_id' => $track->id,
                'user_id' => auth()->id() ?? 1,
                'status' => $status ?: $track->status,
                'note' => null,
            ]);

            $data = [
                "trackingDetails" => [
                    [
                        "eventCode" => self::STATE_MAP[$statusString]['code'],
                        "eventDesc" => self::STATE_MAP[$statusString]['description'],
                        "eventTime" => time(),
                        "location" => "Baku",
                        "originTracking" => [
                            "ase desc" => self::STATE_MAP[$statusString]['description'],
                            "ase code" => $statusString
                        ]
                    ]
                ],
                "waybillNo" => $track->tracking_code
            ];

            $body = json_encode($data);
            $cipher = "cff45d54c13317a6e2965a8ddf899123";
            $hash = $this->generateSignAndCt("dwp.serpens.track_push", "1", $this->user, "1", $body, $cipher);

            $headers = [
                'gw-ver: 1',
                'gw-user-group: ' . $this->user,
                'sign: ' . $hash[0],
                'ct: ' . $hash[1],
                'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
            ];
            $uri = self::CLIENT_URL . "/dwp.serpens.track_push/1";
//            $requestLog = Request::create([
//                'created_at' => now(),
//                'updated_at' => now(),
//                'body' => $body,
//                'method' => 'POST',
//                'uri' => $uri,
//                'request' => json_encode(['headers' => $headers, 'body' => $body]),
//            ]);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $uri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => http_build_query(['data' => $body]),
                CURLOPT_HTTPHEADER => array(
                    'gw-ver: 1',
                    'gw-user-group: ' . $this->user,
                    'sign: ' . $hash[0],
                    'ct: ' . $hash[1],
                    'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
                ),
            ));

            $response = curl_exec($curl);
            $trackStatus->update([
                'note' => $response
            ]);
            curl_close($curl);

//            $requestLog->update([
//                'updated_at' => now(),
//                'response' => $response
//            ]);

            return false;
        } catch (Exception $e) {
            $track->error_txt = $e->getMessage();
            $track->save();
            return false;
        }
    }

    public function createCustomer($params)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $params->consigneeInfo->cellphone);
        $password = rand(10000, 99999);

        return Customer::query()->create([
            'partner_id' => $params->partner_id,
            'name' => $params->consigneeInfo->firstName,
            'surname' => $params->consigneeInfo->lastName,
            'fullname' => $params->consigneeInfo->firstName . ' ' . $params->consigneeInfo->lastName,
            'email' => $params->consigneeInfo->email ?? '',
            'password' => Hash::make($password),
            'address' => $params->consigneeInfo->streetAddress,
            'fin' => $params->consigneeInfo->identify,
            'phone' => "994$phoneNumber",
            'city_name' => $params->consigneeInfo->city,
            'region_name' => $params->consigneeInfo->state,
            'zip_code' => $params->pickUpSiteCode,
        ]);
    }
}
