<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Request;
use App\Models\Surat\SuratPackage;
use App\Models\Track;
use Artisan;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Log;


class SuretSend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Suret {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Suret integration';

    private $apiUrl = "https://suret.dev/partner";
    private $tokenName = "token";
    private $tokenValue = "Tf98BhI6ImHN0v65FHm0tvn76vb45MN0";

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
        $tracks = SuratPackage::query()
//            ->where('id', 68204)
            ->with(['container.suratOffice', 'track.customer'])
            ->whereIn('status', [SuratPackage::STATUSES['NOT_SENT'], SuratPackage::STATUSES['WAITING'], SuratPackage::STATUSES['HAS_PROBLEM']])
            ->where('company_sent',1)
            ->get();

//        $tracks = SuratPackage::query()
//            ->with(['container.suratOffice', 'track.customer'])
//            ->whereIn('barcode',['EQ0000136020AZ','EQ0000136006AZ'])
//            ->where('company_sent',1)
//            ->get();
        $packages = collect();
        $packages = $tracks->merge($packages);
        if ($packages->count() < 1) {
            dd('boshdur');
        }
        $body = [];

        $isCourier = false;

        foreach ($packages as $package) {

            if ($package->type == 'package') {
                $_package = $package->package;
                $customer = $_package->user;
            } else {
                $_package = $package->track;
                $customer = $_package->customer;
            }
            $phone = $customer->phone != "" ? $customer->phone : $_package->phone;

            while (substr($phone, 0, 3) === '994') {
                $phone = substr($phone, 3);
            }
            $phone = '994' . $phone;
            $mobile = $phone;

            $body = [
                "name" => $customer->name != null ? $customer->name : explode(' ', $_package->fullname)[0] ?? '-',
                "surname" => substr(
                    $customer->surname ?? (explode(' ', $_package->fullname)[1] ?? '-'),
                    0,
                    15
                ),
                "mobile" => $mobile,
                "address" => $customer->address,
                "office_id" => $isCourier ? 1 : $package->container->suratOffice->foreign_id,
                "weight" => $_package->weight && $_package->weight != 0 ? $_package->weight : floatval(rand(100, 300) / 1000),
                "tracking_code" => $package->barcode,
                "iscourier" => $isCourier,
            ];

            $body = $this->utf8ize($body);

            $response = $this->sendRequest($body);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            if ($statusCode == 200 && $responseData['datas']) {
                if ($responseData['datas'][0]['success'] == 1 || ($responseData['datas'][0]['success'] == 0 && $responseData['datas'][0]['message'] == "Bu izləmə kodu artıq mövcuddur.")) {
                    SuratPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => SuratPackage::STATUSES['SENT'],
                            'sent_at' => now(),
                            'comment' => "Bağlama Surat Kargo-ya göndərildi!"
                        ]);

                    if ($package->type == 'package') {
                        $_package = Package::find($package->package_id);
                        $_package->bot_comment = "Bağlama Surat Kargo-ya göndərildi!";
                        $_package->save();

                    } else if ($package->type == 'track') {
                        $_track = Track::find($package->package_id);
                        $_track->bot_comment = "Bağlama Surat Kargo-ya göndərildi!";
                        $_track->save();
                    }
                    $this->line("success . Tracking Number: " . $package->barcode);
                    Log::channel('surat')->debug('Success Response from Surat Kargo: ', [$statusCode, $responseData]);
                } else {
                    SuratPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => SuratPackage::STATUSES['HAS_PROBLEM'],
                            'comment' => $package->comment . '| ' . $responseData['datas'][0]['message']
                        ]);
                    $this->warn("Order submission failed for Tracking Number: " . $package->barcode . "--- Error: " . json_encode($responseData));
                }

            }
        }
    }

    public function sendTest()
    {

        $tracks = SuratPackage::whereIn('id',['75371'])
            ->get();





//        $tracks = SuratPackage::query()
//            ->with(['container.suratOffice', 'track.customer'])
//            ->whereIn('barcode',['EQ0000136020AZ','EQ0000136006AZ'])
//            ->where('company_sent',1)
//            ->get();
        $packages = collect();
        $packages = $tracks->merge($packages);
        if ($packages->count() < 1) {
            dd('boshdur');
        }
        $body = [];

        $isCourier = false;

        foreach ($packages as $package) {

            if ($package->type == 'package') {
                $_package = $package->package;
                $customer = $_package->user;
            } else {
                $_package = $package->track;
                $customer = $_package->customer;
            }
            $phone = $customer->phone != "" ? $customer->phone : $_package->phone;

            while (substr($phone, 0, 3) === '994') {
                $phone = substr($phone, 3);
            }
            $phone = '994' . $phone;
            $mobile = $phone;

            $body = [
                "name" => $customer->name != null ? $customer->name : explode(' ', $_package->fullname)[0] ?? '-',
                "surname" => substr(
                    $customer->surname ?? (explode(' ', $_package->fullname)[1] ?? '-'),
                    0,
                    15
                ),
                "mobile" => $mobile,
                "address" => $customer->address,
                "office_id" => $isCourier ? 1 : $package->container->suratOffice->foreign_id,
                "weight" => $_package->weight && $_package->weight != 0 ? $_package->weight : floatval(rand(100, 300) / 1000),
                "tracking_code" => $package->barcode,
                "iscourier" => $isCourier,
            ];

            $body = $this->utf8ize($body);

            dd($body);

            $response = $this->sendRequest($body);
            $statusCode = $response->getStatusCode();
            $responseData = json_decode($response->getBody(), true);
            if ($statusCode == 200 && $responseData['datas']) {
                if ($responseData['datas'][0]['success'] == 1 || ($responseData['datas'][0]['success'] == 0 && $responseData['datas'][0]['message'] == "Bu izləmə kodu artıq mövcuddur.")) {
                    SuratPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => SuratPackage::STATUSES['SENT'],
                            'sent_at' => now(),
                            'comment' => "Bağlama Surat Kargo-ya göndərildi!"
                        ]);

                    if ($package->type == 'package') {
                        $_package = Package::find($package->package_id);
                        $_package->bot_comment = "Bağlama Surat Kargo-ya göndərildi!";
                        $_package->save();

                    } else if ($package->type == 'track') {
                        $_track = Track::find($package->package_id);
                        $_track->bot_comment = "Bağlama Surat Kargo-ya göndərildi!";
                        $_track->save();
                    }
                    $this->line("success . Tracking Number: " . $package->barcode);
                    Log::channel('surat')->debug('Success Response from Surat Kargo: ', [$statusCode, $responseData]);
                } else {
                    SuratPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => SuratPackage::STATUSES['HAS_PROBLEM'],
                            'comment' => $package->comment . '| ' . $responseData['datas'][0]['message']
                        ]);
                    $this->warn("Order submission failed for Tracking Number: " . $package->barcode . "--- Error: " . json_encode($responseData));
                }

            }
        }
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

    private function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        }
        return $mixed;
    }


}


