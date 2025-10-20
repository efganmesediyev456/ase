<?php

namespace App\Console\Commands;

use App\Models\Azerpost\AzerpostPackage;
use App\Models\Package;
use App\Models\Request;
use App\Models\Track;
use Artisan;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Log;


class AzerPoctSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azerPoct {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AzerPoct integration';

    private $apiUrl = "https://online-dev.azerpost.az/api/v1/dashboard";
    private $vendorId = "AS001";
    private $apiKey = "123456789";

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('type') == 'send') {
            $this->send();
        }

        if ($this->option('type') == 'sendTest') {
            $this->sendTest();
        }

    }

    public function send()
    {
        $tracks = AzerpostPackage::query()
            ->with(['container.azerpostOffice', 'track' => function ($q) {
                return $q->with(['customer', 'azerpost_office', 'azeriexpress_office', 'surat_office', 'delivery_point']);
            }])
            ->whereIn('status', [AzerpostPackage::STATUSES['NOT_SENT'], AzerpostPackage::STATUSES['WAITING'], AzerpostPackage::STATUSES['HAS_PROBLEM']])
            ->where('company_sent',1)
//            ->where('azerpost_order_id',2583)
            ->orderBy("id","DESC")
            ->get();
//        $tracks = AzerpostPackage::query()
//            ->with(['container.azerpostOffice', 'track' => function ($q) {
//                return $q->with(['customer', 'azerpost_office', 'azeriexpress_office', 'surat_office', 'delivery_point']);
//            }])
//            ->where('barcode','700655920463000')
//            //->where('azerpost_order_id',2478)
//            ->get();


        $packages = collect();
        $packages = $tracks->merge($packages);

        if ($packages->count() < 1) {
            dd('boshdur');
        }
        $body = [];


        foreach ($packages as $package) {
            //somehow worker adding precint packages to azerpost container
            $_package = $package->type == 'package' ? $package->package : $package->track;
            $user = $package->type == 'package' ? $_package->user : $_package->customer;
            $phone = $user->phone != "" ? $user->phone : $_package->phone;

            if (!isset($_package->azerpost_office->name)) {
                $this->line('azerpost aid olmayan baglama: '.$package->barcode);
                continue;
            }

            while (substr($phone, 0, 3) === '994') {
                $phone = substr($phone, 3);
            }
            $phone = '994' . $phone;
            $mobile = $phone;
            $fullName = isset($user->fullname) ? explode(' ', $user->fullname) : [];
            $firstName = (!empty($fullName) && isset($fullName[0])) ? $fullName[0] : null;
            $lastName = (!empty($fullName) && isset($fullName[1])) ? $fullName[1] : null;
            $body = [
                "vendor_id" => env('AZERPOST_VENDOR_ID', $this->vendorId),
                "package_id" => $package->barcode,
                "delivery_post_code" => $_package->azerpost_office->name,
                "package_weight" => $_package->weight ?? 0.1,
                "customer_address" => $_package->address ?? $user->address,
                "first_name" => $firstName != null ? $firstName : $user->name,
                "last_name" => substr(
                    $lastName ?? $user->surname,
                    0,
                    15
                ),
                "email" => $user->email,
                "phone_no" => $mobile,
                "user_passport" => $user->fin,
                "fragile" => 0,
                "delivery_type" => 0,//$_package->delivery_manat_price_discount,
                "vendor_payment" => $_package->delivery_manat_price_discount ?? 0.1,
                "vendor_payment_status" => $package->payment_status,
            ];
//            dd($body);
            $response = $this->sendRequest($body);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
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
                $this->line("success . Tracking Number: " . $package->barcode);
            } else {
                if (isset($responseData['error'], $responseData['error']['text']) && $responseData['error']['text'] == 'active_package_found') {
                    AzerpostPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => AzerpostPackage::STATUSES['SENT'],
                            'sent_at' => now(),
                            'foreign_order_id' => $responseData['error']['package_id']
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
                    $this->line("success already exists . Tracking Number: " . $package->barcode);
                }else{
                    AzerpostPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => AzerpostPackage::STATUSES['HAS_PROBLEM'],
                            'comment' => "-" . $response->getStatusCode() . '|' . json_encode($responseData)
                        ]);
                    $this->warn("Order submission failed for Tracking Number: " . $package->barcode . "--- Error: " . json_encode($responseData));
                }
            }

        }
    }


    public function sendTest()
    {
        $tracks = AzerpostPackage::query()->whereIn('barcode',['250619351985000','901124490113000','250619351985000'])
            ->get();

//        dd("salam", $tracks->pluck('id'));
//        $tracks = AzerpostPackage::query()
//            ->with(['container.azerpostOffice', 'track' => function ($q) {
//                return $q->with(['customer', 'azerpost_office', 'azeriexpress_office', 'surat_office', 'delivery_point']);
//            }])
//            ->where('barcode','700655920463000')
//            //->where('azerpost_order_id',2478)
//            ->get();


        $packages = collect();
        $packages = $tracks->merge($packages);

        if ($packages->count() < 1) {
            dd('boshdur');
        }
        $body = [];


        foreach ($packages as $package) {
            //somehow worker adding precint packages to azerpost container
            $_package = $package->type == 'package' ? $package->package : $package->track;
            $user = $package->type == 'package' ? $_package->user : $_package->customer;
            $phone = $user->phone != "" ? $user->phone : $_package->phone;

            if (!isset($_package->azerpost_office->name)) {
                $this->line('azerpost aid olmayan baglama: '.$package->barcode);
                continue;
            }

            while (substr($phone, 0, 3) === '994') {
                $phone = substr($phone, 3);
            }
            $phone = '994' . $phone;
            $mobile = $phone;
            $fullName = isset($user->fullname) ? explode(' ', $user->fullname) : [];
            $firstName = (!empty($fullName) && isset($fullName[0])) ? $fullName[0] : null;
            $lastName = (!empty($fullName) && isset($fullName[1])) ? $fullName[1] : null;
            $body = [
                "vendor_id" => env('AZERPOST_VENDOR_ID', $this->vendorId),
                "package_id" => $package->barcode,
                "delivery_post_code" => $_package->azerpost_office->name,
                "package_weight" => $_package->weight ?? 0.1,
                "customer_address" => $_package->address ?? $user->address,
                "first_name" => $firstName != null ? $firstName : $user->name,
                "last_name" => substr(
                    $lastName ?? $user->surname,
                    0,
                    15
                ),
                "email" => $user->email,
                "phone_no" => $mobile,
                "user_passport" => $user->fin,
                "fragile" => 0,
                "delivery_type" => 0,//$_package->delivery_manat_price_discount,
                "vendor_payment" => $_package->delivery_manat_price_discount ?? 0.1,
                "vendor_payment_status" => $package->payment_status,
            ];
            $response = $this->sendRequest($body);



            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
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
                $this->line("success . Tracking Number: " . $package->barcode);
            } else {
                if (isset($responseData['error'], $responseData['error']['text']) && $responseData['error']['text'] == 'active_package_found') {
                    AzerpostPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => AzerpostPackage::STATUSES['SENT'],
                            'sent_at' => now(),
                            'foreign_order_id' => $responseData['error']['package_id']
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
                    $this->line("success already exists . Tracking Number: " . $package->barcode);
                }else{
                    AzerpostPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => AzerpostPackage::STATUSES['HAS_PROBLEM'],
                            'comment' => "-" . $response->getStatusCode() . '|' . json_encode($responseData)
                        ]);
                    $this->warn("Order submission failed for Tracking Number: " . $package->barcode . "--- Error: " . json_encode($responseData));
                }
            }

        }
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
            'http_errors' => false,
        ]);
    }

}


