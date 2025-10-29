<?php

namespace App\Services\Integration;

use App\Models\IntegrationStatusSend;
use App\Models\PackageGood;
use App\Models\Request;
use App\Models\RuType;
use App\Models\Track;
use App\Models\TrackStatus;
use App\Models\UnitradePackage;
use App\Services\Package\PackageService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Log;


class UnitradeService extends BaseService
{
    const STATE_MAP = [
        'CustomsClearance' => 301,
        'CustomsCompleted' => 350,
        'Sorting' => 401,
        'PudoAccepted' => 404,
        'AtPudo' => 450,
        'Delivered' => 10000,
        'StoppedInCustoms' => 307,
        'RTO' => 20050,
        'DeliveredByCourier' => 10001
    ];

    const PLACE = [
        'CustomsClearance' => "d040b7c0-6a91-4916-906b-06d066f8b063",
        'CustomsCompleted' => "d040b7c0-6a91-4916-906b-06d066f8b063",
        'Sorting' => "67e81fdf-d6c5-428c-9b30-6d73a0b7b786",
        'PudoAccepted' => "67e81fdf-d6c5-428c-9b30-6d73a0b7b786",
        'AtPudo' => "67e81fdf-d6c5-428c-9b30-6d73a0b7b786",
        'Delivered' => "67e81fdf-d6c5-428c-9b30-6d73a0b7b786",
        'DeliveredByCourier' => "67e81fdf-d6c5-428c-9b30-6d73a0b7b786",
        'StoppedInCustoms' => "d040b7c0-6a91-4916-906b-06d066f8b063",
        'RTO' => "67e81fdf-d6c5-428c-9b30-6d73a0b7b786",

    ];

    const CLIENT_AUTH_URL = 'https://oauth.unitrade.space';
    const CLIENT_URL = 'https://api.unitrade.space';
    const CLIENT_ID = 'ase_az';
    const CLIENT_SECRET = 'kqqCGmGMNyPWNPSdSyDmrkHnpCJonFFP';

    private $token;
    private $client;

    public function __construct()
    {
        $this->token = $this->getAccessToken();
        $this->client = curl_init();
    }

    private function getAccessToken()
    {
        $token = DB::table('tokens')->where('name', 'api_access_token')->first();

        if ($token && $token->expires_at > now()) {
            return $token->access_token;
        }

        $client = new Client();
        $response = $client->post(self::CLIENT_AUTH_URL . '/connect/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'client_id' => self::CLIENT_ID,
                'client_secret' => self::CLIENT_SECRET,
                'grant_type' => 'client_credentials',
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $expiresAt = now()->addSeconds($data['expires_in']);

        DB::table('tokens')->updateOrInsert(
            ['name' => 'api_access_token'],
            [
                'access_token' => $data['access_token'],
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]
        );

        return $data['access_token'];
    }

    public function createPackage($params, $user): array
    {
        $statusId = $params->weight ? self::STATES['WaitingForDeclaration'] : self::STATES['WaitingDomesticShipment'];
        $from_country = isset($params->from_country) ? $params->from_country : 'RU';
        $warehouse = $params->warehouse_id;
        $warehousePrefix = substr($warehouse, 0, 2);
        $warehouseId = substr($warehouse, 2);

        $track = Track::create([
            "partner_id" => self::PARTNERS_MAP[$params->partner ?: 'OZON'],
            "customer_id" => $user->id,
            "from_country" => $from_country,
            "warehouse_id" => self::WAREHOUSES[strtolower($params->partner ?: 'OZON')],
            "tracking_code" => $params->delivery_number,
            "status" => $statusId,
            "website" => self::PARTNER_WEBSITES[$params->partner ?: 'OZON'],
            "number_items" => count($params['products']) ?? 1,
            "weight" => null,
            "delivery_price" => $params->shipping_invoice['invoice_price'],
            "delivery_price_cur" => $params->shipping_invoice['currency'],
            "delivery_price_status" => $params->shipping_invoice['status']??null,
            "currency" => $params->shipping_invoice['currency'],
            "fullname" => $params->buyer['first_name'] . ' ' . $params->buyer['last_name'],
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
            "yenipoct_office_id" => $warehousePrefix === "YP" && !$params->is_door ? $warehouseId : null,
            'paid' => true,
            'latitude' => $params->buyer['latitude'] ?? null,
            'longitude' => $params->buyer['longitude'] ?? null,
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
        $detailedType = [];

        foreach ($params['products'] as $product) {
            $hs_code = trim((string) $product['hs_code']);

            $ruType = RuType::updateOrCreate(
                ['hs_code' => $hs_code],
                ['name_ru' => $product['category'] . ' - ' . $product['name']]
            );

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
            $detailedType[] = $product['quantity'] . " x " . $product['name'];
        }

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
                "invoice_price" => $track->shipping_invoice_price,
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
            'shipping_invoice_price' => $request->shipping_invoice['invoice_price']
        ]);

        $data = [];
        if ($request->weight && $request->weight != 0) {
            if($request->weight_only){
                $track->update([
                    'weight' => floatval($request->weight) / 1000
                ]);
            }else {
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
        }

        return $this->prepareResponse($unitradePackage);
    }

    public function updatePackagetest($unitradePackageQuery, $request)
    {
        $unitradePackage = $unitradePackageQuery->first();
        $track = Track::query()->where('id', $unitradePackage->track_id)->first();

        $unitradePackageUpdates = [];

        if (isset($request->weight)) {
            $unitradePackageUpdates['weight'] = floatval($request->weight) / 1000;
        }

        if (isset($request->shipping_invoice['invoice_price'])) {
            $unitradePackageUpdates['shipping_invoice_price'] = $request->shipping_invoice['invoice_price'];
        }

        if (!empty($unitradePackageUpdates)) {
            $unitradePackageQuery->update($unitradePackageUpdates);
        }

        if (isset($request->weight) && $request->weight != 0) {
            if ($request->weight_only) {
                $track->update([
                    'weight' => floatval($request->weight) / 1000,
                ]);
            } else {
                $trackUpdates = [
                    'weight' => floatval($request->weight) / 1000,
                ];

                if (isset($request->shipping_invoice['invoice_price'])) {
                    $trackUpdates['delivery_price'] = $request->shipping_invoice['invoice_price'];
                }
                if (isset($request->buyer)) {
                    $trackUpdates['fullname'] = $request->buyer['first_name'] . ' ' . $request->buyer['last_name'];
                    $trackUpdates['fin'] = $request->buyer['pin_code'];
                    $trackUpdates['phone'] = $request->buyer['phone_number'];
                    $trackUpdates['email'] = $request->buyer['email_address'];
                    $trackUpdates['address'] = $request->buyer['shipping_address'];
                    $trackUpdates['zip_code'] = $request->buyer['zip_code'];
                }
                if (isset($request->shipping_invoice['currency'])) {
                    $trackUpdates['currency'] = $request->shipping_invoice['currency'];
                }
                if (isset($request->invoice['invoice_price'])) {
                    $trackUpdates['shipping_amount'] = $request->invoice['invoice_price'];
                    $trackUpdates['shipping_amount_cur'] = self::CURRENCIES[$request->invoice['currency']];
                }

                $trackUpdates['status'] = self::STATES['WaitingForDeclaration'];

                $track->update($trackUpdates);

                if (isset($request->products)) {
                    foreach ($request->products as $product) {
                        $ruType = RuType::query()->firstOrCreate(
                            ['hs_code' => $product['hs_code']],
                            ['name_ru' => $product['category'] . ' - ' . $product['name']]
                        );

                        PackageGood::query()->updateOrCreate(
                            ['track_id' => $track->id],
                            [
                                'number_items' => $product['quantity'],
                                'ru_type_id' => $ruType->id,
                                'shipping_amount' => $product['unit_price'],
                                'shipping_amount_cur' => self::CURRENCIES[$request['invoice']['currency']],
                            ]
                        );
                    }
                }

                $unitradePackageQuery->update(['status' => self::STATES['WaitingForDeclaration']]);
            }
        }

        return $this->prepareResponse($unitradePackage);
    }



    public function updateStatusTest(Track $track, $status = null,$date = null)
    {
        Log::channel('unitrade_status')->debug($track->tracking_code.' is started', [
            'tracking_code'        => $track->tracking_code,
            'status'               => $status,
            'date'                 => $date
        ]);




        if ($this->token == "") {
            $track->error_txt = $track->error_txt . "| empty token: $this->token | ";
            $track->save();
        }
        if ($status == 16) {
            $this->updateStatus($track, 24, $date);
        }
        $client = new Client();
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
            $uri = self::CLIENT_URL . "/v3/tracking/status";
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ];
            $body = [[
                "trackNumber" => $track->tracking_code,
                "place" => self::PLACE[$statusString],
                "eventCode" => self::STATE_MAP[$statusString],
                "moment" => now('UTC')->format('Y-m-d\TH:i:s.v\Z')
            ]];

            Log::channel('unitrade_status')->debug($track->tracking_code.' body', [
                'body'                 => $body,
                'headers'              => $headers,
                'uri'                  => $uri
            ]);


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
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);

//            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//            dd([
//                'code' => $responseCode,
//                'response' => $response
//            ]);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);


            Log::channel('unitrade_status')->debug($track->tracking_code.' response', [
                'response'             => $response,
                'responseCode'         => $responseCode,
            ]);
            $trackStatus->update([
                'note' => $response
            ]);
            curl_close($curl);
            return true;
        } catch (Exception $e) {
            $track->error_txt = $track->error_txt . "| " . $e->getMessage() . " ";
            $track->save();
            Log::channel('unitrade_status')->error($track->tracking_code.' response', [
                'message'              => $e->getMessage(),
            ]);
            return false;
        }
    }


    public function updateStatus(Track $track, $status = null,$date = null)
    {
        Log::channel('unitrade_status')->debug($track->tracking_code.' is started', [
            'tracking_code'        => $track->tracking_code,
            'status'               => $status,
            'date'                 => $date
        ]);

        if ($this->token == "") {
            $track->error_txt = $track->error_txt . "| empty token: $this->token | ";
            $track->save();
        }
        if ($status == 16) {
            $this->updateStatus($track, 24, $date);
        }
        $client = new Client();
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
            $uri = self::CLIENT_URL . "/v3/tracking/status";
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ];
            $body = [[
                "trackNumber" => $track->tracking_code,
                "place" => self::PLACE[$statusString],
                "eventCode" => self::STATE_MAP[$statusString],
                "moment" => now('UTC')->format('Y-m-d\TH:i:s.v\Z')
            ]];


            Log::channel('unitrade_status')->debug($track->tracking_code.' body', [
                'body'                 => $body,
                'headers'              => $headers,
                'uri'                  => $uri
            ]);


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
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);

//            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//            dd([
//                'code' => $responseCode,
//                'response' => $response
//            ]);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);


            Log::channel('unitrade_status')->debug($track->tracking_code.' response', [
                'response'             => $response,
                'responseCode'         => $responseCode,
            ]);
            $trackStatus->update([
                'note' => $response
            ]);
            curl_close($curl);
            return true;
        } catch (Exception $e) {
            $track->error_txt = $track->error_txt . "| " . $e->getMessage() . " ";
            $track->save();
            Log::channel('unitrade_status')->error($track->tracking_code.' response', [
                'message'              => $e->getMessage(),
            ]);
            return false;
        }
    }
    public function updateStatusNew(Track $track, $status = null)
    {
        if ($status == 16) {
            $this->updateStatusNew($track, 24);
        }

        $integrationStatusSend = new IntegrationStatusSend();
        $integrationStatusSend->partner_id = 3;
        $integrationStatusSend->parcel_id = $track->id;
        $integrationStatusSend->parcel_type = 'track';
        $integrationStatusSend->parcel_barcode = $track->tracking_code;
        $integrationStatusSend->admin_id = auth()->id() ?? 1;
        $integrationStatusSend->status = $status ?: $track->status;
        $integrationStatusSend->save();

        return true;
    }
    public function updateStatusV3(Track $track, $status = null)
    {
        if ($this->token == "") {
            $track->error_txt = $track->error_txt . "| empty token: $this->token | ";
            $track->save();
            return [
                'success' => false,
                'request_body' => null,
                'response' => 'Unauthorized'
            ];
        }
        if ($status == 16) {
            $this->updateStatusV3($track, 24);
        }

        $client = new Client();

        if ($status) {
            $statusString = array_search((int)$status, self::STATES, true);
        } else {
            $statusString = array_search($track->status, self::STATES, true);
        }

        if ($statusString === false || ($statusString && !array_key_exists($statusString, self::STATE_MAP))) {
            $track->error_txt = $track->error_txt . "{ status not found: $status | " . $track->status . " | $statusString }";
            $track->save();

            //status not found, no need to send this status to partner if not exists in STATES
            return [
                'success' => false,
                'request_body' => null,
                'response' => 'STATUS_NOT_FOUND'
            ];
        }

        try {
            $trackStatus = TrackStatus::query()->create([
                'track_id' => $track->id,
                'user_id' => auth()->id() ?? 1,
                'status' => $status ?: $track->status,
                'note' => null,
            ]);

            $uri = self::CLIENT_URL . "/v3/tracking/status";
            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ];
            $body = [[
                "trackNumber" => $track->tracking_code,
                "place" => self::PLACE[$statusString],
                "eventCode" => self::STATE_MAP[$statusString],
                "moment" => now('UTC')->format('Y-m-d\TH:i:s.v\Z')
            ]];

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
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            ));

            $response = curl_exec($curl);

            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            $trackStatus->update([
                'note' => $response
            ]);

            curl_close($curl);
            return [
                'success' => $responseCode >= 200 && $responseCode < 300,
                'request_body' => json_encode($body),
                'response' => $response
            ];
        } catch (Exception $e) {
            $track->error_txt = $track->error_txt . "| " . $e->getMessage() . " ";
            $track->save();
            return [
                'success' => false,
                'request_body' => null,
                'response' => $e->getMessage()
            ];
        }
    }

}
