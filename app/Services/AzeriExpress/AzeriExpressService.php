<?php

namespace App\Services\AzeriExpress;

use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\AzeriExpress\AzeriExpressOrder;
use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Package;
use App\Models\Track;
use App\Services\Interfaces\PackageServiceInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class AzeriExpressService implements PackageServiceInterface
{
    const STATUSES_IDS = [
        "WAITING" => 0,
        "SENT" => 1,
        "WAREHOUSE" => 2,
        "ONWAY" => 3,
        "ARRIVEDTOPOINT" => 4,
        "DELIVERED" => 5,
        "NOT_DELIVERED" => 6,
        "" => 7,
        "COURIER_ASSIGNED" => 8,
    ];

    const AZERI_COURIER_STATUSES = [
        "WAITING" => 0,
        "SENT" => 1,
        "WAREHOUSE" => 30,  // 2
        "ONWAY" => 31,    // 3
        "ARRIVEDTOPOINT" => 32, //4
        "DELIVERED" => 33,  //5
        "NOT_DELIVERED" => 34,  //6
        "" => 35, //7
        "COURIER_ASSIGNED" => 36, //8
    ];

    const STATUS_MAP = [
        2 => "WAREHOUSE",
        3 => "ONWAY",
        4 => "ARRIVEDTOPOINT",
        5 => "DELIVERED",
        6 => "NOT_DELIVERED",
    ];

    const REASONS = [
        1 => 'Müştəriyə zəng çatmır',
        2 => 'Müştəri sifarişdən imtina etdi',
        3 => 'Müştəri ünvanda deyil',
        4 => 'Sifariş müştəriyə aid deyil',
        5 => 'Ünvan dəqiq/tam deyil'
    ];
    private $apiUrl = "https://api.azeriexpress.com";
    private $testEmail = "apitest@azeriexpress.com";
    private $testPassword = "123456789";
    private $token;

    public function sendPackage($package)
    {
        $this->login();

        Log::channel('azeriexpress')->debug('Package preparing for sending to Azeriexpress: ', [$package]);
        $_package = $this->prepareOrderData($package);
        Log::channel('azeriexpress')->debug('Prepared data for sending to Azeriexpress: ', [$_package]);
        try {
            $response = $this->sendRequest($_package);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            if ($statusCode == 201 && $responseData['data']) {
                AzeriExpressPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => AzeriExpressPackage::STATUSES['SENT'],
                        'sent_at' => now()
                    ]);

                if ($package->type == 'package') {
                    $_package = Package::find($package->package_id);
                    $_package->bot_comment = "Bağlama AzerExpress-ə göndərildi";
                    $_package->save();

                } else if ($package->type == 'track') {
                    $_track = Track::find($package->package_id);
                    $_track->bot_comment = "Bağlama AzerExpress-ə göndərildi";
                    $_track->save();
                }
                Log::channel('azeriexpress')->debug('Success Response from Azeriexpress: ', [$statusCode, $responseData]);
            } else {
                AzeriExpressPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => AzeriExpressPackage::STATUSES['HAS_PROBLEM'],
                        'comment' => $package->comment . '| ' . json_encode([$responseData['data']])
                    ]);
                Log::channel('azeriexpress')->error('Fail Response from Azeriexpress: ', [$statusCode, $responseData]);
            }
        } catch (Exception $e) {
            AzeriExpressPackage::query()
                ->where('id', $package->id)
                ->update([
                    'comment' => $e->getMessage()
                ]);
            Log::channel('azeriexpress')->error('Response from Azeriexpress: ', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    private function login()
    {
        $token = DB::table('tokens')->where('name', 'azeriexpress_pudo_token')->first();

        if ($token && $token->expires_at > now()) {
            return $this->token = $token->access_token;
        }
        $token = $this->requestNewToken();

        DB::table('tokens')->updateOrInsert(
            ['name' => 'azeriexpress_pudo_token'],
            [
                'access_token' => $token,
                'expires_at' => now()->addHours(6),
                'updated_at' => now(),
            ]
        );

        return $this->token = $token;
    }

    private function requestNewToken()
    {
        $client = new Client();
        $data = [
            "email" => env('AZERIEXPRESS_EMAIL', $this->testEmail),
            "password" => env('AZERIEXPRESS_PASSWORD', $this->testPassword),
        ];
        Log::channel('azeriexpress')->debug("Azeriexpress login data: ", $data);

        $response = $client->post($this->apiUrl . "/login", [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $data,
        ]);
        $statusCode = $response->getStatusCode();
        $responseData = json_decode($response->getBody(), true);

        if ($statusCode == 200 && $responseData['success'] === true && $responseData['user']) {
            return $responseData['user']['api_token'];
        }

        Log::channel('azeriexpress')->error("Azeriexpress login error: ", $responseData);
        return false;
    }

    private function prepareOrderData($package)
    {
        if ($package->type == 'package') {
            $_package = $package->package;
            $customer = $_package->user;
        } else {
            $_package = $package->track;
            $customer = $_package->customer;
        }
        return [
            "post_office_id" => $_package->azeriexpress_office->foreign_id,
            "payment_method" => 1,
            "is_paid" => ($package->type == 'package') ? $package->paid : $package->payment_status,
            "customer_number" => $package->type == 'package' ? $customer->customer_id : "ASE" . $customer->id,
            "customer_passport" => $customer->passpot,//
            "customer_fincode" => $customer->fin,
            "customer_name" => $customer->first_name ?: explode(' ', $customer->fullname)[0],
            "customer_surname" => $customer->last_name ?: explode(' ', $customer->fullname)[1],
            "customer_mobile" => $customer->phone != "" ? $customer->phone : $_package->phone,
            "address" => $customer->address,
            "barcode" => $package->barcode,
            "weight" => $_package->weight ?? 0.111,
            "price" => 0.8,
            "package_contents" => $_package->tracking_code,
        ];
    }

    private function sendRequest($order)
    {
        $client = new Client();
        return $client->post($this->apiUrl . "/integration/orders", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
            'json' => $order,
        ]);
    }

    public function sendContainer($container)
    {
        $this->login();
        $client = new Client();
        $data = [
            "container_number" => "Test123456",
            "packages" => ["ALMA000054051"]
        ];
        return $client->post($this->apiUrl . "/integration/orders", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
            'json' => $order,
        ]);
    }

    public function getBranches($page = 1)
    {
        $this->login();
        $client = new Client();
        $return = [];
        try {
            $response = $client->get($this->apiUrl . "/integration/post-offices", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ]
            ]);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            if ($statusCode == 200 && $responseData['post_offices']) {
                foreach ($responseData['post_offices'] as $item) {
                    AzeriExpressOffice::query()->updateOrCreate([
                        'foreign_id' => $item['id'],
                    ], [
                        'foreign_id' => $item['id'],
                        'name' => $item['name']
                    ]);
                }
            } else {
                Log::info("Error Response during getting post offices from Azeriexpress: ", [$responseData]);
            }
        } catch (Exception $e) {
            Log::info("Error Response during handling get post offices from Azeriexpress: ", [$e->getMessage()]);
        }
    }

    public function updateOrderPayment($barcode)
    {
        $orderQuery = AzeriExpressPackage::query()->where('barcode', $barcode);
        if ($orderQuery->exists()) {
            $this->login();
            $client = new Client();
            $response = $client->put($this->apiUrl . "/integration/orders/" . $barcode . '/payment', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ],
                'json' => array(
                    'is_paid' => true
                ),
            ]);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            $orderQuery->update([
                'payment_status' => true,
            ]);
            Log::info('Response from Azeriexpress for Update Order Payment: ', [$responseData, 'status' => $statusCode]);

            return true;
        }

        return false;
    }

    public function getContainer($officeId)
    {
        $office = AzeriExpressOffice::query()->where('id', $officeId)->first();
        $container = AzeriExpressOrder::query()
//            ->where('azeri_express_office_id', $office->id)
            ->where('for_courier', false)
            ->where('status', AzeriExpressOrder::STATUSES['WAITING'])
            ->latest()
            ->first();
        if (!$container) {
            $authId = Auth::user()->id;
            $barcode = $this->generateNewBarcode();
            $container = AzeriExpressOrder::query()->create([
                'name' => "EX" . '-' . now()->format('d-m-Y H:i'),
                'user_id' => $authId,
                'azeri_express_office_id' => $office->id,
                'status' => AzeriExpressOrder::STATUSES['WAITING'],
                'barcode' => $barcode,
                'created_at' => now()
            ]);
        }

        return $container;
    }
    public function getCourierContainer($officeId)
    {
        $office = AzeriExpressOffice::query()->where('id', $officeId)->first();
        $container = AzeriExpressOrder::query()
//            ->where('azeri_express_office_id', $office->id)
            ->where('for_courier', true)
            ->whereDate('created_at', now())
            ->latest()
            ->first();
        if (!$container) {
            $authId = Auth::user()->id;
            $barcode = $this->generateNewBarcode();
            $container = AzeriExpressOrder::query()->create([
                'name' => "EX" . '-' . now()->format('d-m-Y H:i'),
                'user_id' => $authId,
                'azeri_express_office_id' => $office->id ?? null,
                'status' => AzeriExpressOrder::STATUSES['WAITING'],
                'barcode' => $barcode,
                'for_courier' => true,
                'created_at' => now()
            ]);
        }

        return $container;
    }

    public function generateNewBarcode(): string
    {
        // Generate a random 8-digit number
        $newBarcode = "ASEX" . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

        $exist = AzeriExpressOrder::query()->where('barcode', $newBarcode)->exists();
        if ($exist) {
            return $this->generateNewBarcode();
        }

        return $newBarcode;
    }

    public function validatePackage($package, $type = null,$return = false)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        $exists = AzeriExpressPackage::query()->where('package_id', $package->id)->where('type',$packageType)->exists();
        if ($exists) {
            return "Bağlama artıq konteynerə əlavə olunub!";
        }

        return null;
    }

    public function createPackage($container, $package)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        AzeriExpressPackage::query()->updateOrCreate([
            'package_id' => $package->id,
            'barcode' => $package->tracking,
            'type' => $packageType
        ], [
            'azeri_express_order_id' => $container->id,
            'user_id' => $package->user_id,
            'package_id' => $package->id,
            'status' => AzeriExpressPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package->tracking,
            'payment_status' => $package->paid
        ]);

        AzeriExpressOrder::query()
            ->where('id', $container->id)
            ->update([
                'weight' => floatval($container->weight) + floatval($package->weight),
            ]);

        if ($packageType == 'package') {
            $_package = Package::find($package->id);
            $_package->bot_comment = "Bağlama AzeriExpress Konteynerə($container->id) əlavə olundu";
            $_package->save();
        }

        if ($packageType == 'track') {
            $_track = Track::find($package->id);
            $_track->comment_txt = "Bağlama AzeriExpress Konteynerə($container->id) əlavə olundu";
            $_track->save();
        }
    }

    public function createCourierPackage($container, $track)
    {
        AzeriExpressPackage::query()->updateOrCreate([
            'package_id' => $track->id,
            'barcode' => $track->tracking,
            'type' => 'track'
        ], [
            'azeri_express_order_id' => $container->id,
            'user_id' => $track->user_id,
            'package_id' => $track->id,
            'status' => AzeriExpressPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $track->tracking,
            'payment_status' => $track->paid
        ]);

        AzeriExpressOrder::query()
            ->where('id', $container->id)
            ->update([
                'weight' => floatval($container->weight) + floatval($track->weight),
            ]);

        $_track = Track::find($track->id);
        $_track->comment_txt = "Bağlama AzeriExpress Kuryer Konteynerə($container->id) əlavə olundu";
        $_track->save();
    }
}
