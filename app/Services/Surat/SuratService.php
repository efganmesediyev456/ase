<?php

namespace App\Services\Surat;

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

class SuratService implements PackageServiceInterface
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
    private $apiUrl = "https://suret.dev/partner";
    private $tokenName = "token";
    private $tokenValue = "Tf98BhI6ImHN0v65FHm0tvn76vb45MN0";

    public function sendPackage($suratPackage)
    {
        Log::channel('surat')->debug('Packages preparing for sending to Surat Kargo: ', [$suratPackage]);
        try {
            $_package = $this->preparePackageData($suratPackage);
            $response = $this->sendRequest($_package);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            Log::channel('surat')->debug('Response from Surat Kargo: ', [$_package, $statusCode, $responseData]);
            if ($statusCode == 200 && $responseData['datas']) {
                if ($responseData['datas'][0]['success'] == 1 || ($responseData['datas'][0]['success'] == 0 && $responseData['datas'][0]['message'] == "Bu izləmə kodu artıq mövcuddur.")) {
                    SuratPackage::query()
                        ->where('id', $suratPackage->id)
                        ->update([
                            'status' => SuratPackage::STATUSES['SENT'],
                            'sent_at' => now(),
                            'comment' => "Bağlama Surat Kargo-ya göndərildi!"
                        ]);

                    if ($suratPackage->type == 'package') {
                        $_package = Package::find($suratPackage->package_id);
                        $_package->bot_comment = "Bağlama Surat Kargo-ya göndərildi!";
                        $_package->save();

                    } else if ($suratPackage->type == 'track') {
                        $_track = Track::find($suratPackage->package_id);
                        $_track->bot_comment = "Bağlama Surat Kargo-ya göndərildi!";
                        $_track->save();
                    }
                    Log::channel('surat')->debug('Success Response from Surat Kargo: ', [$statusCode, $responseData]);
                } else {
                    SuratPackage::query()
                        ->where('id', $suratPackage->id)
                        ->update([
                            'status' => SuratPackage::STATUSES['HAS_PROBLEM'],
                            'comment' => $suratPackage->comment . '| ' . $responseData['datas'][0]['message']
                        ]);
                }
            } else {
                SuratPackage::query()
                    ->where('id', $suratPackage->id)
                    ->update([
                        'status' => SuratPackage::STATUSES['HAS_PROBLEM'],
                        'comment' => $suratPackage->comment . '| ' . json_encode([$responseData])
                    ]);
                Log::channel('surat')->error('Fail Response from Surat Kargo: ', [$statusCode, $responseData]);
            }
        } catch (Exception $e) {
            SuratPackage::query()
                ->where('id', $suratPackage->id)
                ->update([
                    'status' => SuratPackage::STATUSES['HAS_PROBLEM'],
                    'comment' => $e->getMessage()
                ]);
            Log::channel('surat')->error('Response from Surat Kargo: ', ['code' => $e->getCode(), 'message' => $e->getMessage()]);
        }
    }

    private function preparePackageData($suratPackage)
    {
        if ($suratPackage->type == 'package') {
            $_package = $suratPackage->package;
            $customer = $_package->user;
        } else {
            $_package = $suratPackage->track;
            $customer = $_package->customer;
        }
        $isCourier = false;
        return [
//            "is_paid" => $suratPackage->payment_status,
//            "customer_fincode" => $customer->fin,
            "name" => $customer->name != null ? $customer->name : explode(' ', $_package->fullname)[0] ?? '-',
            "surname" => $customer->surname != null ? $customer->surname : explode(' ', $_package->fullname)[1] ?? '-',
            "mobile" => $customer->phone != "" ? $customer->phone : $_package->phone,
            "address" => $customer->address,
            "office_id" => $isCourier ? 1 : $suratPackage->container->suratOffice->foreign_id,
            "weight" => $_package->weight && $_package->weight != 0 ? $_package->weight : floatval(rand(100, 300) / 1000),
            "tracking_code" => $suratPackage->barcode,
            "iscourier" => $isCourier,
        ];
    }

    private function sendRequest($packageData)
    {
        Log::channel('surat')->debug('Url: ' . $this->apiUrl . "/addBundle", ['data' => [$packageData]]);

        $client = new Client();
        return $client->post($this->apiUrl . "/addBundle", [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                $this->tokenName => $this->tokenValue
            ],
            'json' => [
                $packageData
            ],
        ]);
    }

    public function validatePackage($package, $type = null,$return = false )
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        $exists = SuratPackage::query()->where('package_id', $package->id)->where('type',$packageType)->exists();
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
            $_package->bot_comment = "Bağlama Surat Kargo Konteynerə($container->id) əlavə olundu";
            $_package->save();
        }

        if ($packageType == 'track') {
            $_track = Track::find($package->id);
            $_track->comment_txt = "Bağlama Surat Kargo Konteynerə($container->id) əlavə olundu";
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
                'barcode' => $this->generateNewBarcode(),
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
}
