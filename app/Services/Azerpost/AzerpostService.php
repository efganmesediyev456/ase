<?php

namespace App\Services\Azerpost;

use App\Models\Azerpost\AzerpostOffice;
use App\Models\Azerpost\AzerpostOrder;
use App\Models\Azerpost\AzerpostPackage;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Request;
use App\Models\Track;
use App\Services\Interfaces\PackageServiceInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Log;

class AzerpostService implements PackageServiceInterface
{
    const STATUSES = [
        0 => "HAS_PROBLEM",
        1 => "NOT_SENT",
        2 => "WAITING",
        3 => "SENT",
        4 => "IN_PROCESS",
        6 => "WAREHOUSE",
        8 => "ARRIVEDTOPOINT",
        10 => "DELIVERED",
    ];
    const STATUS_MAP = [
        0 => "SENT",
        1 => "IN_PROCESS",
        2 => "WAREHOUSE",
        3 => "WAREHOUSE",
        4 => "ARRIVEDTOPOINT",
        5 => "DELIVERED",
        6 => "HAS_PROBLEM",
    ];

    private $apiUrl = "https://online-dev.azerpost.az/api/v1/dashboard";
    private $vendorId = "AS001";
    private $apiKey = "123456789";

    public function sendContainer($container)
    {
//        $this->login();
        $client = new Client();
        $data = [
            "container_number" => "Test123456",
            "packages" => ["ALMA000054051"]
        ];
        return $client->post(env('AZERPOST_API_URL', $this->apiUrl) . "/integration/orders", [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/json',
            ],
            'json' => $order,
        ]);
    }

    public function updateOrderPayment($barcode)
    {
        $orderQuery = AzerpostPackage::query()->where('barcode', $barcode);
        if ($orderQuery->exists()) {
            $client = new Client();
            $response = $client->put(env('AZERPOST_API_URL', $this->apiUrl) . '/order/vp-status', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => env('AZERPOST_API_KEY', $this->apiKey),
                ],
                'json' => array(
                    'vendor_id' => env('AZERPOST_VENDOR_ID', $this->vendorId),
                    'package_id' => $barcode,
                    'vendor_payment_status' => 1
                ),
            ]);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);

            $orderQuery->update([
                'payment_status' => true,
            ]);
            Log::info('Response from Azerpost for Update Order Payment: ', [$responseData, 'status' => $statusCode]);

//            return $return;
        }
    }

    public function getContainer($officeId)
    {
        $office = AzerpostOffice::query()->where('id', $officeId)->first();
        $container = AzerpostOrder::query()
//            ->where('azerpost_office_id', $office->id)
            ->where('status', AzerpostOrder::STATUSES['WAITING'])
            ->latest()
            ->first();
        if (!$container) {
            $authId = Auth::user()->id;
            $barcode = $this->generateNewBarcode();
            $container = AzerpostOrder::query()->create([
                'name' => "AZ" . '-' . now()->format('d-m-Y H:i'),
                'user_id' => $authId,
                'azerpost_office_id' => $office->id,
                'status' => AzerpostOrder::STATUSES['WAITING'],
                'barcode' => $barcode,
                'created_at' => now()
            ]);
        }

        return $container;
    }

    public function generateNewBarcode(): string
    {
        // Generate a random 8-digit number
        $newBarcode = "ASEX" . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

        $exist = AzerpostOrder::query()->where('barcode', $newBarcode)->exists();
        if ($exist) {
            return $this->generateNewBarcode();
        }

        return $newBarcode;
    }

    public function validatePackage($package, $type = 'package',$return = false)
    {
        if (!$package->paid) {
            if ($type == 'package') {
                Notification::sendPackage($package->id, 'package_not_paid');
            } else {
                Notification::sendTrack($package->id, 'package_not_paid');
            }

            return ['status' => false, 'message' => 'Package&Track ödənilməyib!'];
        }
        $packageType = $package instanceof Package ? 'package' : 'track';

        $exists = AzerpostPackage::query()->where('package_id', $package->id)->where('type',$packageType)->exists();
        if ($exists) {
            return "Bağlama artıq konteynerə əlavə olunub!";
        }

        return null;
    }

    public function sendPackage($package)
    {
        $_package = $this->prepareOrderData($package);

        try {
            $uri = env('AZERPOST_API_URL', $this->apiUrl) . "/order/create";
//            $requestLog = Request::create([
//                'created_at' => now(),
//                'updated_at' => now(),
//                'body' => json_encode($_package),
//                'method' => 'POST',
//                'uri' => $uri,
//                'request' => json_encode(['headers' => [], 'body' => $_package]),
//            ]);
            $response = $this->sendRequest($_package);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
//            $requestLog->update([
//                'updated_at' => now(),
//                'response' => $response->getBody()
//            ]);
            Log::channel('azerpost')->debug('Response from Azerpost:', [
                'status' => $statusCode,
                'body' => $response->getBody(),
            ]);
            if ($statusCode == 200 && $responseData['status']) {
                AzerpostPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => AzerpostPackage::STATUSES['SENT'],
                        'sent_at' => now(),
                        'foreign_order_id' => $responseData['order_Id']
                    ]);

                if ($package->type == 'package') {
                    $_package = Package::find($package->package_id);
                    $_package->bot_comment = "Bağlama Azərpost-a göndərildi";
                    $_package->save();

                }
                if ($package->type == 'track') {
                    $_track = Track::find($package->package_id);
                    $_track->comment_txt = "Bağlama Azərpost-a göndərildi";
                    $_track->save();
                }
            } else {
                AzerpostPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => AzerpostPackage::STATUSES['HAS_PROBLEM'],
                        'comment' => "-" . $response->getStatusCode() . '|' . json_encode($responseData)
                    ]);
            }
        } catch (Exception $e) {
            Log::channel('azerpost')->error('Response from Azerpost:', [
                'message' => $e->getMessage(),
                'context' => json_encode([$_package]),
            ]);
        }
    }

    private function prepareOrderData($package)
    {
        $_package = $package->type == 'package' ? $package->package : $package->track;
        $user = $package->type == 'package' ? $_package->user : $_package->customer;

        $fullName = isset($package->fullname) ? explode(' ', $package->fullname) : [];
        $firstName = (!empty($fullName) && isset($fullName[0])) ? $fullName[0] : null;
        $lastName = (!empty($fullName) && isset($fullName[1])) ? $fullName[1] : null;
        return [
            "vendor_id" => env('AZERPOST_VENDOR_ID', $this->vendorId),
            "package_id" => $package->barcode,
            "delivery_post_code" => $_package->azerpost_office->name,
            "package_weight" => $_package->weight ?? 0.1,
            "customer_address" => $_package->address ?? $user->address,
            "first_name" => $firstName != null ? $firstName : $user->name,
            "last_name" => $lastName != null ? $lastName : $user->surname,
            "email" => $user->email,
            "phone_no" => $user->phone != "" ? $user->phone : $_package->phone,
            "user_passport" => $user->fin,
            "fragile" => 0,
            "delivery_type" => 0,//$_package->delivery_manat_price_discount,
            "vendor_payment" => $_package->delivery_manat_price_discount ?? 0.1,
            "vendor_payment_status" => $package->payment_status,
        ];
    }

    private function sendRequest($order)
    {
        $client = new Client();
        $url = env('AZERPOST_API_URL', $this->apiUrl);
        return $client->post($url . "/order/create", [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => env('AZERPOST_API_KEY', $this->apiKey),
            ],
            'json' => $order,
            'verify' => false,
        ]);
    }

    public function createPackage($container, $package)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        AzerpostPackage::query()->updateOrCreate([
            'package_id' => $package->id,
            'barcode' => $package->tracking,
            'type' => $packageType
        ], [
            'azerpost_order_id' => $container->id,
            'user_id' => $package->user_id,
            'package_id' => $package->id,
            'status' => AzerpostPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package->tracking,
            'payment_status' => $package->paid
        ]);

        AzerpostOrder::query()
            ->where('id', $container->id)
            ->update([
                'weight' => floatval($container->weight) + floatval($package->weight),
            ]);

        if ($packageType == 'package') {
            $_package = Package::find($package->id);
            $_package->bot_comment = "Bağlama Azerpost Konteynerə($container->id) əlavə olundu";
            $_package->save();
        }

        if ($packageType == 'track') {
            $_track = Track::find($package->id);
            $_track->comment_txt = "Bağlama Azerpost Konteynerə($container->id) əlavə olundu";
            $_track->save();
        }
    }
}
