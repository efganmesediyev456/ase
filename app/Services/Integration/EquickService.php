<?php

namespace App\Services\Integration;

use App\Models\PackageGood;
use App\Models\RuType;
use App\Models\Track;
use App\Models\TrackStatus;
use App\Models\UnitradePackage;
use Exception;

class EquickService extends BaseService
{
    const STATE_MAP = [
        'CustomsClearance' => "CustomsClearance",
        'CustomsCompleted' => "CustomsCompleted",
        'Sorting' => "Sorting",
        'AtPudo' => "AtPudo",
        'Delivered' => "Delivered",
        'DeliveredByCourier' => "DeliveredByCourier",
        'Undelivered' => "Undelivered"
    ];

    const CLIENT_URL = 'https://api.equick.cn/api/webhooks/tracking/ase_tracking';

    private $client;

    public function __construct()
    {
        $this->client = curl_init();
    }

    public function createPackage($params, $user): array
    {
        $statusId = $params->weight ? self::STATES['WaitingForDeclaration'] : self::STATES['WaitingDomesticShipment'];

        $warehouse = $params->warehouse_id;
        $warehousePrefix = substr($warehouse, 0, 2);
        $warehouseId = substr($warehouse, 2);

        $track = Track::create([
            "partner_id" => self::PARTNERS_MAP[$params->partner ?: 'OZON'],
            "customer_id" => $user->id,

            "warehouse_id" => self::WAREHOUSES[strtolower($params->partner ?: 'OZON')],
            "tracking_code" => $params->delivery_number,
            "internal_tracking_number" => $params->internal_tracking_number ?? null,
            "status" => $statusId,
            "website" => self::PARTNER_WEBSITES[$params->partner ?: 'OZON'],
            "number_items" => count($params['products']) ?? 1,
            "weight" => null,
            "delivery_price" => $params->shipping_invoice['invoice_price'],
            "delivery_price_cur" => $params->shipping_invoice['currency'],
            "delivery_price_status" => $params->shipping_invoice['status'] ?? null,
            "currency" => $params->shipping_invoice['currency'],
            "fullname" => $params->buyer['first_name'],
            "fin" => $params->buyer['pin_code'],
            "phone" => $params->buyer['phone_number'],
            "email" => $params->buyer['email_address'],
            "address" => $params->buyer['shipping_address'],
            "zip_code" => $params->buyer['zip_code'],
            "delivery_type" => $params->is_door ? "HD" : "PUDO",
            "shipping_amount" => $params->invoice['invoice_price'],
            "shipping_amount_cur" => self::CURRENCIES[$params->invoice['currency']],
            "store_status" => $warehousePrefix === "DP" && !$params->is_door ? $warehouseId : null,
            "azeriexpress_office_id" => $warehousePrefix === "EX" && !$params->is_door ? $warehouseId : null,
            "azerpost_office_id" => $warehousePrefix === "AZ" && !$params->is_door ? $warehouseId : null,
            "surat_office_id" => $warehousePrefix === "SR" && !$params->is_door ? $warehouseId : null,
            "kargomat_office_id" => $warehousePrefix === "KR" && !$params->is_door ? $warehouseId : null,
            'paid' => $params->shipping_invoice['status'] ?? false,
        ]);

        $unitradePackage = UnitradePackage::create([
            'package_id' => null,
            'track_id' => $track->id,
            'user_id' => null,
            'customer_id' => $user->id,
            'uid' => $params->uid,
            'warehouse_id' => 12,
            'delivery_number' => $params->delivery_number,
            'comment' => $params->comment,
            'is_liquid' => $params->is_liquid,
            'is_door' => $params->is_door,
            'seller_name' => $params->seller['full_name'] ?? null,
            'seller_email' => $params->seller['email_address'] ?? null,
            'seller_phone' => $params->seller['phone_number'] ?? null,
            'seller_address' => $params->seller['address'] ?? null,
            'seller_ioss_number' => $params->seller['IOSS_number'] ?? null,
            'seller_country' => $params->seller['country'] ?? null,
            'seller_city' => $params->seller['IOSS_number'] ?? null,
            'invoice_price' => $params->invoice['invoice_price'] ?? null,
            'invoice_due_date' => $params->invoice['invoice_due_date'] ?? null,
            'invoice_url' => $params->invoice['invoice_url'] ?? null,
            'invoice_currency' => $params->invoice['currency'] ?? null,
            'shipping_invoice_price' => $params->shipping_invoice['invoice_price'] ?? null,
            'shipping_invoice_due' => $params->shipping_invoice['invoice_due_date'] ?? null,
            'shipping_invoice_url' => $params->shipping_invoice['invoice_url'] ?? null,
            'shipping_currency' => $params->shipping_invoice['currency'] ?? null,
            'request_json' => json_encode($params->all()),
            'status' => $statusId,
            'buyer_city' => $params->buyer['city'] ?? null,
            'buyer_country' => $params->buyer['country'] ?? null,
            'buyer_phone_number' => $params->buyer['phone_number'] ?? null,
            'buyer_region' => $params->buyer['region'] ?? null,
            'buyer_email_address' => $params->buyer['email_address'] ?? null,
            'buyer_first_name' => $params->buyer['first_name'] ?? null,
            'buyer_last_name' => $params->buyer['last_name'] ?? null,
            'buyer_zip_code' => $params->buyer['zip_code'] ?? null,
            'buyer_pin_code' => $params->buyer['pin_code'] ?? null,
            'buyer_shipping_address' => $params->buyer['shipping_address'] ?? null,
            'buyer_billing_address' => $params->buyer['billing_address'] ?? null,
        ]);

        $total_shipping_amount = 0;
        $total_number_items = 0;
        $total_weight = 0;

        foreach ($params['products'] as $product) {
            $ruType = RuType::query()->where('hs_code', $product['hs_code'])->first();

            if (!$ruType) {
                $ruType = new RuType();
                $ruType->hs_code = $product['hs_code'];
                $ruType->name_ru = $product['category'] . ' - ' . $product['name'];
                $ruType->save();
            }

            PackageGood::create([
                'track_id' => $track->id,
                'number_items' => $product['quantity'],
                'weight' => floatval(($product['weight'] ?? 0) / 1000),
                'ru_type_id' => $ruType->id,
                'shipping_amount' => $product['unit_price'],
                'shipping_amount_cur' => self::CURRENCIES[$params['invoice']['currency']],
                'country_id' => $track->country_id,
                'warehouse_id' => 12
            ]);

            $total_shipping_amount += $product['unit_price'] * $product['quantity'];
            $total_number_items += $product['quantity'];
            $total_weight += 0;
//            $detailedType[] = $product['quantity'] . " x " . ($ruType->name_ru ?? $product['name']);
            $detailedType[] = $product['quantity'] . " x " . $product['name'];
        }

        // Calculate and update totals
        $track->detailed_type = implode("; ", $detailedType);
        $track->save();

        return $this->prepareResponse($unitradePackage);
    }

    public function prepareResponse($track): array
    {
        return [
            "package_id" => $track->track_id,
            "delivery_number" => $track->delivery_number,
            "status" => array_search($track->status, self::STATES),
            "is_liquid" => $track->is_liquid,
            "buyer" => [
                "city" => $track->customer->city,
                "country" => $track->buyer_country,
                "phone_number" => $track->buyer_phone_number,
                "billing_address" => $track->buyer_billing_address,
                "first_name" => $track->buyer_first_name,
                "last_name" => $track->buyer_last_name,
                "email_address" => $track->buyer_email_address,
                "zip_code" => $track->buyer_zip_code,
                "pin_code" => $track->fin,
                "shipping_address" => $track->passport_fin
            ],
            "seller" => [
                "full_name" => $track->seller_name,
                "city" => null,
                "country" => null,
                "phone_number" => $track->seller_phone,
                "IOSS_number" => $track->seller_ioss_number,
                "email_address" => $track->seller_email,
                "address" => $track->seller_address,
                "zip_code" => null
            ],
            "comment" => $track->comment,
            "is_door" => $track->is_door,
            "domestic_cargo_company" => $track->domestic_cargo_company,
            "invoice" => [
                "invoice_price" => $track->invoice_price,
                "currency" => $track->shipping_currency,
                "invoice_due_date" => $track->shipping_invoice_due,
                "invoice_url" => $track->shipping_invoice_url
            ],
            "shipping_invoice" => [
                "invoice_price" => $track->shipping_invoice_price,
                "currency" => $track->shipping_currency,
                "invoice_due_date" => $track->shipping_invoice_due,
                "invoice_url" => $track->shipping_invoice_url
            ],
            "current_state" => null,
            "uid" => $track->uid,
            "created_date" => $track->created_at->toDateTimeString(),
//            "warehouse" => [
//                "id" => FilialController::PREFIX . $track->filial_id,
//                "name" => $track->filial->name ?? null,
//                "description" => $track->filial->name ?? "tapılmadı"
//            ],
        ];
    }

    public function updatePackage($unitradePackageQuery, $request)
    {
        //update unitrade packages table
        $unitradePackage = $unitradePackageQuery->first();
        $track = Track::query()->where('id', $unitradePackage->track_id)->first();

        //throw an error if not exists
        $unitradePackageQuery->update([
            'weight' => floatval($request->weight) / 1000,
            'shipping_invoice_price' => $request->shipping_invoice['invoice_price'],
            'invoice_price' => $request->invoice['invoice_price'],
        ]);

        $data = [];
        if ($request->weight && $request->weight != 0) {
            $track->update([
                'weight' => floatval($request->weight) / 1000,
                'delivery_price' => $request->shipping_invoice['invoice_price'],
                'status' => self::STATES['WaitingForDeclaration'],
                "fullname" => $request->buyer['first_name'] . ' ' . $request->buyer['last_name'],
                "fin" => $request->buyer['pin_code'],
                "phone" => $request->buyer['phone_number'],
                "email" => $request->buyer['email_address'],
                "address" => $request->buyer['shipping_address'],
                "zip_code" => $request->buyer['zip_code'],
                "currency" => $request->shipping_invoice['currency'],
                "shipping_amount" => $request->invoice['invoice_price'],
                "shipping_amount_cur" => self::CURRENCIES[$request->invoice['currency']],
            ]);

            foreach ($request['products'] as $product) {
                $ruType = RuType::query()->where('hs_code', $product['hs_code'])->first();

                if (!$ruType) {
                    $ruType = new RuType();
                    $ruType->hs_code = $product['hs_code'];
                    $ruType->name_ru = $product['category'] . ' - ' . $product['name'];
                    $ruType->save();
                }

                PackageGood::query()->updateOrCreate(
                    [
                        'track_id' => $track->id,
                    ],
                    [
                        'number_items' => $product['quantity'],
                        'ru_type_id' => $ruType->id,
                        'shipping_amount' => $product['unit_price'],
                        'shipping_amount_cur' => self::CURRENCIES[$request['invoice']['currency']],
                    ]
                );
            }

            $unitradePackageQuery->update([
                'status' => self::STATES['WaitingForDeclaration'],
            ]);
        }
        $updatedPackage = $unitradePackageQuery->first();
        return $this->prepareResponse($updatedPackage);
    }

    public function updateStatus(Track $track, $status = null)
    {
        if ($status) {
            $statusString = array_search((int)$status, self::STATES, true);
        } else {
            $statusString = array_search($track->status, self::STATES, true);
        }
        if ($statusString === false || ($statusString && !array_key_exists($statusString, self::STATE_MAP))) {
            $track->error_txt = $track->error_txt . "{ status not found: $status | " . $track->status . " | $statusString }";
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
            $headers = [
                'Content-Type' => 'application/json',
            ];
            $body = [[
                "parcel_ids" => [$track->tracking_code],
                "author" => auth()->id() ?? "-",
                "comment" => "",
                "state" => self::STATE_MAP[$statusString],
                "date" => now('UTC')->format('Y-m-d H:i')
            ]];
//            $requestLog = Request::create([
//                'created_at' => now(),
//                'updated_at' => now(),
//                'body' => json_encode($body),
//                'method' => 'POST',
//                'uri' => self::CLIENT_URL,
//                'request' => json_encode(['headers' => $headers, 'body' => $body]),
//            ]);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => self::CLIENT_URL,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                ),
            ));

            $response = curl_exec($curl);
            $trackStatus->update([
                'note' => $response
            ]);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//            $requestLog->update([
//                'updated_at' => now(),
//                'response' => $responseCode . ':' . $response,
//            ]);
            curl_close($curl);
            return true;
        } catch (Exception $e) {
            $track->error_txt = $track->error_txt . "| " . $e->getMessage() . " ";
            $track->save();
            return false;
        }
    }
}
