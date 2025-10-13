<?php

namespace App\Services\Kuryera;

use App\Models\Package;
use App\Models\Surat\SuratOffice;
use App\Models\Surat\SuratOrder;
use App\Models\Surat\SuratPackage;
use App\Models\Track;
use App\Services\Interfaces\PackageServiceInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Log;

class KuryeraService implements PackageServiceInterface
{
    const STATUSES_IDS = [
        "WAITING" => 0,
        "SENT" => 1,
        "WAREHOUSE" => 2,

        "ACCEPTED" => 5,
        "COURIER_ASSIGNED" => 7,
        "OUT_FOR_DELIVERY" => 34,
        "DELIVERED" => 9,
        "NOT_DELIVERED" => 35,
    ];

    const REASONS = [
        1 => 'Müştəriyə zəng çatmır',
        2 => 'Müştəri sifarişdən imtina etdi',
        3 => 'Müştəri ünvanda deyil',
        4 => 'Sifariş müştəriyə aid deyil',
        5 => 'Ünvan dəqiq/tam deyil'
    ];

    const REASONS_MAP = [
        1 => 'TTC47',
        2 => 'TTC48',
        3 => 'TTC55',
        4 => 'TTC37',
        5 => 'TTC36',
        6 => 'TTC33',
        7 => 'TTC30',
    ];

    private $apiUrl = "https://courier.saas.az/api/v10/integration/";
    private $tokenName = "apiKey";
    private $tokenValue = "234sfsdf32m4n2kj3423j4jkb423b4j23";

    public function sendPackage($csPackage)
    {
        Log::channel('cs')->debug('Packages preparing for sending to Kuryera: ', [$csPackage]);
        try {
            $message = "";
            $_package = $this->preparePackageData($csPackage);
            $response = $this->sendRequest($_package);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            Log::channel('cs')->debug('Response from Kuryera: ', [$_package, $statusCode, $responseData]);
            if ($statusCode == 200 && $responseData['datas']) {
                SuratPackage::query()
                    ->where('id', $csPackage->id)
                    ->update([
                        'status' => SuratPackage::STATUSES['SENT'],
                        'sent_at' => now(),
                        'comment' => "Bağlama Kuryera-a göndərildi!"
                    ]);

                if ($csPackage->type == 'package') {
                    $_package = Package::find($csPackage->package_id);
                    $_package->bot_comment = "Bağlama Kuryera-a göndərildi!";
                    $_package->save();

                } else if ($csPackage->type == 'track') {
                    $_track = Track::find($csPackage->package_id);
                    $_track->bot_comment = "Bağlama Kuryera-a göndərildi!";
                    $_track->save();
                }
                Log::channel('cs')->debug('Success Response from Kuryera: ', [$statusCode, $responseData]);
            }

            return ['success' => true, "Order sent"];
        } catch (Exception $e) {
            $message = $e->getMessage();
            Log::channel('cs')->error('Response from Kuryera: ', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }

        return ['success' => false, 'message' => "Order not sent! $message"];
    }

    private function preparePackageData($csPackages)
    {
        foreach ($csPackages as $csPackage) {

            if ($csPackage->type === 'package') {
                $_package = $csPackage->package;
                $customer = $_package->user;
            } else {
                $_package = $csPackage->track;
                $customer = $_package->customer;
            }

            $data['data'][] = [
                "hub_uuid" => $csPackage->container->office ?? "-",
                "payment_method" => 1,
                "pickup" => [
                    "uuid" => $csPackage->pickup->uuid ?? "SR11",
                    "phone" => $csPackage->pickup->phone ?? "994556130398",
                    "address" => $csPackage->pickup->address ?? "Baku, Azerbaijan, 11b",
                    "lat" => $csPackage->pickup->lat ?? "40.491918",
                    "long" => $csPackage->pickup->long ?? "49.811518"
                ],
                "customer" => [
                    "code" => $customer->code ?? "ASE123",
                    "firstname" => $customer->name ?? explode(' ', $_package->fullname)[0] ?? '-',
                    "lastname" => $customer->surname ?? explode(' ', $_package->fullname)[1] ?? '-',
                    "passport" => $customer->passport ?? "AA3503246",
                    "fincode" => $customer->fin ?? "68Q6Z57",
                    "phone" => $customer->phone ?? "+994556130398"
                ],
                "parcel" => [
                    "barcode" => $_package->barcode ?? "ASE1234577",
                    "weight" => $_package->weight ?? floatval(rand(100, 300) / 1000),
                    "price" => $_package->price ?? 150,
                    "currency" => $_package->currency ?? "USD",
                    "cod_amount" => $_package->cod_amount ?? 10,
                    "cod_charge" => $_package->cod_charge ?? false,
                    "is_paid" => $csPackage->payment_status ?? false,
                    "lat" => $_package->lat ?? "40.494918",
                    "long" => $_package->long ?? "49.83518",
                    "address" => $_package->address ?? "Baku, Azerbaijan, 245a"
                ]
            ];
        }

        return $data;
    }

    private function sendRequest($packageData)
    {
        Log::channel('cs')->debug('Url: ' . $this->apiUrl . "parcel", ['data' => $packageData]);

        $client = new Client();
        return $client->post($this->apiUrl . "parcel", [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                $this->tokenName => $this->tokenValue
            ],
            'json' => [
                "data" => $packageData
            ],
        ]);
    }

    public function validatePackage($package, $type = null)
    {
        $exists = SuratPackage::query()->where('package_id', $package->id)->exists();
        if ($exists) {
            return "Bağlama artıq konteynerə əlavə olunub!";
        }

        return null;
    }

    public function createPackage($container, $package)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        SuratPackage::query()->updateOrCreate([
            'package_id' => $package->id,
            'barcode' => $package->tracking,
            'type' => $packageType
        ], [
            'surat_order_id' => $container->id,
            'user_id' => $package->user_id,
            'package_id' => $package->id,
            'status' => SuratPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package->tracking,
            'payment_status' => $package->paid
        ]);

        SuratOrder::query()
            ->where('id', $container->id)
            ->update([
                'weight' => floatval($container->weight) + floatval($package->weight),
            ]);

        if ($packageType == 'package') {
            $_package = Package::find($package->id);
            $_package->bot_comment = "Bağlama Kuryera Konteynerə($container->id) əlavə olundu";
            $_package->save();
        }

        if ($packageType == 'track') {
            $_track = Track::find($package->id);
            $_track->comment_txt = "Bağlama Kuryera Konteynerə($container->id) əlavə olundu";
            $_track->save();
        }
    }

    public function updateOrderPayment($barcode)
    {

    }

    public function getContainer($officeId)
    {
        $office = SuratOffice::query()->where('id', $officeId)->first();
        $container = SuratOrder::query()
            ->where('surat_office_id', $office->id)
            ->where('status', SuratOrder::STATUSES['WAITING'])
            ->latest()
            ->first();
        if (!$container) {
            $authId = Auth::user()->id;
            $container = SuratOrder::query()->create([
                'name' => $office->name . '-' . now()->format('d-m-Y'),
                'user_id' => $authId,
                'surat_office_id' => $office->id,
                'status' => SuratOrder::STATUSES['WAITING'],
                'barcode' => (new KuryeraService())->generateNewBarcode(),
                'created_at' => now()
            ]);
        }

        return $container;
    }

    public function generateNewBarcode(): string
    {
        $newBarcode = "ASEX" . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

        $exist = SuratOrder::query()->where('barcode', $newBarcode)->exists();
        if ($exist) {
            return $this->generateNewBarcode();
        }

        return $newBarcode;
    }

    public function createOrder($order)
    {
        $message = "";
        Log::channel('cs')->debug('Packages preparing for sending to Kuryera: ', [$order]);
        try {
            $response = $this->sendRequest($order);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            Log::channel('cs')->debug('Response from Kuryera: ', [$order, $statusCode, $responseData]);
            if ($statusCode == 200 && $responseData['data']) {
                Log::channel('cs')->debug('Success Response from Kuryera: ', [$statusCode, $responseData]);
            }

            return ['success' => true, "Order sent"];
        } catch (Exception $e) {
            $message = $e->getMessage();
            Log::channel('cs')->error('Response from Kuryera: ', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }

        return ['success' => false, 'message' => "Order not sent! $message"];
    }
}
