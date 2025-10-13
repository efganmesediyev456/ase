<?php

namespace App\Console\Commands;

use App\Models\CD;
use App\Models\Customer;
use App\Models\Extra\Whatsapp;
use App\Models\NotificationQueue;
use App\Models\User;
use App\Services\Integration\UnitradeService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Models\Filial;
use Carbon\Carbon;
use App\Models\Extra\SMS;
use App\Models\Track;
use App\Models\CA;
use App\Models\Package;
use App\Models\CustomsModel;
use App\Services\Package\PackageService;
use App\Models\Extra\Notification;
use App\Models\ExclusiveLock;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'test';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->token = $this->getAccessToken();
        $this->client = curl_init();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    const CLIENT_AUTH_URL = 'https://oauth.unitrade.space';
    const CLIENT_URL = 'https://api.unitrade.space';
    const CLIENT_ID = 'ase_az';
    const CLIENT_SECRET = 'kqqCGmGMNyPWNPSdSyDmrkHnpCJonFFP';
    private $token;
    private $client;


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

    public function handle()
    {


        $uri = "https://api.unitrade.space/v3/tracking/status";

        $body = [[
            "trackNumber" => '850568601271000',
            "place" => '67e81fdf-d6c5-428c-9b30-6d73a0b7b786',
            "eventCode" => 10001,
            "moment" => now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
        ]];

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: ' . 'Bearer ' . $this->token,
        ];

        $maxAttempts = 5;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxAttempts && !$success) {
            $attempt++;

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $uri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => $headers,
            ]);

            $response = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $decoded = json_decode($response, true) ?? [];

            if (array_key_exists('errors', $decoded)
                and array_key_exists(0, $decoded['errors'])
                and $decoded['errors'][0]['code'] != 'unknown'
            ) {
                $success = true;
                break;
            }else{
                $success = false;
            }

            if ($attempt < $maxAttempts) {
                echo "Attempt {$attempt} failed (HTTP $httpCode). Retrying in 5 minutes...\n";
                sleep(4);
            }

        }

        $decoded = json_decode($response, true);


        dd($response);


//        $body = [
//            'trackingNumber' => 'EQ0000200166AZ',
//        ];
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/carriersposts/0/100',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS => json_encode($body),
//            CURLOPT_HTTPHEADER => array(
//                'accept: application/json',
//                'lang: az',
//                'ApiKey: 8CD0F430D478F8E1DFC8E1311B20031E3A669607',
//                'Content-Type: application/json'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        curl_close($curl);
//        $response = json_decode($response,true);
//        dd($response);

//        $package = Track::where('id', 522591)->first();
//        if ($package->type == 'package') {
//            $_package = $package->package;
//            $customer = $_package->user;
//        } else {
//            $_package = $package->track;
//            $customer = $_package->customer;
//        }
//        $phone = $customer->phone != "" ? $customer->phone : $_package->phone;
//        $phone = str_replace(' ', '', $phone);
//
//        if (!isset($_package->azeriexpress_office->name)) {
//            $this->line('azeriexpress aid olmayan baglama: '.$package->barcode);
//        }
//
//        $body = [
//            "post_office_id" => $_package->azeriexpress_office->foreign_id,
//            "payment_method" => 1,
//            "is_paid" => $package->payment_status,
//            "customer_number" => $package->type == 'package' ? $customer->customer_id : "ASE" . $customer->id,
//            "customer_passport" => $customer->passpot,//
//            "customer_fincode" => $customer->fin,
//            "customer_name" => $customer->first_name ?: explode(' ', $customer->fullname)[0],
//            "customer_surname" => substr(
//                $customer->last_name ?: (explode(' ', $customer->fullname)[1] ?? '-'),
//                0,
//                15
//            ),
//            "customer_mobile" => $phone,
//            "address" => $customer->address,
//            "barcode" => $package->barcode,
//            "weight" => $_package->weight ?? 0.111,
//            "price" => 0.8,
//            "package_contents" => $_package->tracking_code,
//        ];
//
//        $token = $this->getToken();
//        if (!$token[1]['api_token']) {
//            dd('3rd party token error');
//        }
//
//        $postfield = json_encode($body);
//        $url = 'https://api.azeriexpress.com/integration/orders';
//        $multiCurl = curl_init();
//        curl_setopt($multiCurl, CURLOPT_URL, $url);
//        curl_setopt($multiCurl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($multiCurl, CURLOPT_CUSTOMREQUEST, "POST");
//        curl_setopt($multiCurl, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($multiCurl, CURLOPT_SSL_VERIFYHOST, false);
//        curl_setopt($multiCurl, CURLOPT_POSTFIELDS, $postfield);
//        curl_setopt($multiCurl, CURLOPT_HTTPHEADER, array(
//            'accept: text/plain',
//            "lang: az",
//            "Content-Type: application/json",
//            "Authorization: Bearer " . $token[1]['api_token']
//        ));
//
//        $data = curl_exec($multiCurl);
//        $httpCode = curl_getinfo($multiCurl, CURLINFO_HTTP_CODE);
//        curl_close($multiCurl);
//        if ($data === false) {
//            $result = curl_error($multiCurl);
//        } else {
//            $result = $data;
//        }
//        $response = json_decode($data, true);
//        dd($response);
//        $body = [
////            "trackingNumber"=> 'KGO9920215439302'
//            "trackingNumber"=> 'ASE4112615445397'
//        ];
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/deleteddeclarations/0/100',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS =>json_encode($body),
//            CURLOPT_HTTPHEADER => array(
//                'accept: application/json',
//                'lang: az',
//                'ApiKey: 8CD0F430D478F8E1DFC8E1311B20031E3A669607',
//                'Content-Type: application/json'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
////        $response = json_decode($response,true);
//        dd($response);



//        $body          = [
////            "status" => 0,
//            "trackingNumber" => "ASE3556048787552"
////            'OSS-1001899885'
////            "dateFrom"       => $date_from."501Z",
////            "dateTo"         => $date_to."501Z",
//        ];
//        $regNumbers     = [];
//        $statuses       = [3,8];
//        $approvesearch  = [];
//        $addToBoxBodies = [];
//        $addToboxes     = null;
//
////        $body = [
//
////            "dateFrom"       => "2023-08-16T07:11:10.501Z",
////            "dateTo"         => "2023-08-16T07:11:10.501Z",
////            "documentNumber" => "string",
////            "trackingNumber" => "string",
////            "status"         => 0
////        ];
//        $curl = curl_init();
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/declarations/0/100',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS =>json_encode($body),
//            CURLOPT_HTTPHEADER => array(
//                'accept: application/json',
//                'lang: az',
//                'ApiKey: 8CD0F430D478F8E1DFC8E1311B20031E3A669607',
//                'Content-Type: application/json'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//        dd($response);
//        die('declarations');
//
//        $body = [
//            "trackingNumber"=> 'EQ0000129904AZ'
//        ];
//        $curl = curl_init();
//
//        curl_setopt_array($curl, array(
//            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/declarations/0/100',
//            CURLOPT_RETURNTRANSFER => true,
//            CURLOPT_ENCODING => '',
//            CURLOPT_MAXREDIRS => 10,
//            CURLOPT_TIMEOUT => 0,
//            CURLOPT_FOLLOWLOCATION => true,
//            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//            CURLOPT_CUSTOMREQUEST => 'POST',
//            CURLOPT_POSTFIELDS =>json_encode($body),
//            CURLOPT_HTTPHEADER => array(
//                'accept: application/json',
//                'lang: az',
//                'ApiKey: 8CD0F430D478F8E1DFC8E1311B20031E3A669607',
//                'Content-Type: application/json'
//            ),
//        ));
//
//        $response = curl_exec($curl);
//
//        curl_close($curl);
////        $response = json_decode($response,true);
//        dd($response);

//        Artisan::call('carriers:update', [
//            'package' => 1,
//            'package_id' => 311312,
//            'checkonly' => 0,
//            'htmlformat' => 0,
//            'deleteonly' => 1
//        ]);

//        Artisan::call('carriers:update', [
//            'package' => 1,
//            'package_id' => 311312,
//            'checkonly' => 0,
//            'htmlformat' => 0
//        ]);






//        $customers = Customer::query()->whereNull('courier_id')->get();
//
//        foreach ($customers as $customer) {
//            $prevTrack = Track::where('customer_id', $customer->id)
//                ->where('status',17)
//                ->whereNotNull('courier_delivery_id')
//                ->orderByDesc('id')
//                ->with('courier_delivery')
//                ->first();
//
//            if ($prevTrack && $prevTrack->courier_delivery) {
//                $customer->courier_id = $prevTrack->courier_delivery->courier_id;
//                $customer->save();
//
//                $this->info("Courier assigned to customer ID {$customer->id}");
//            } else {
//                $this->warn("No valid track for customer ID {$customer->id}");
//            }
//        }


//        $tracks = Track::query()
//            ->where('delivery_type', 'HD')
//            ->whereIn('partner_id', [9, 1])
//            ->whereDoesntHave('courier_delivery')
//            ->whereNull('courier_delivery_id')->get();

//        $tracks = Track::query()
//            ->where('delivery_type', 'HD')
//            ->whereIn('partner_id', [9, 1])
//            ->whereNull('courier_delivery_id')
//            ->where('created_at', '>=', '2025-07-01 00:00:00')
//            ->get();
//
//        foreach ($tracks as $track) {
//            if ($track->debt_price > 0 && $track->paid_debt == 0) {
//                $this->error("Items have debt price for track: {$track->id}");
//                continue;
//            }
//
//            if ($track->customer_id) {
//                $customer = $track->customer;
//                $courier_id = null;
//
//                $prevTrack = Track::where('customer_id', $customer->id)
//                    ->whereHas('courier_delivery')
//                    ->where('status', 17)
//                    ->where('id', '!=', $track->id)
//                    ->with('courier_delivery')
//                    ->orderByDesc('id')
//                    ->first();
//
//                if (
//                    $prevTrack &&
//                    $prevTrack->courier_delivery &&
//                    $prevTrack->courier_delivery->address === $customer->address
//                ) {
//                    $courier_id = $customer->courier_id ?? $prevTrack->courier_delivery->courier_id;
//                }
//
//                $cd_status = 1;
//                $cd = $track->courier_delivery;
//
//                if ($cd) {
//                    $cd_status = $cd->status;
//                }
//
//                $str = $track->worker_comments;
//
//                if (isOfficeWord($str)) {
//                    CD::removeTrack($cd, $track);
//                    $this->info("Track {$track->id} removed from delivery.");
//                    continue;
//                }
//
//                if ($cd && (($cd->courier_id != $courier_id) || ($cd->address != $customer->address))) {
//                    $cd = CD::updateTrack($cd, $track, $courier_id);
//                }
//
//                if (!$cd) {
//                    $cd = CD::newCD($track, $courier_id, $cd_status);
//                }
//
//                $cd->save();
//                $track->courier_delivery_id = $cd->id;
//                $track->save();
//
//                if (!$customer->courier_id) {
//                    $customer->courier_id = $courier_id;
//                    $customer->save();
//                }
//
//                $this->info('Courier assigned');
//            }
//        }




//        $tracks = Track::where('status', 17)
//            ->where('created_at', '>=', Carbon::parse('2025-06-15 00:00:00'))
//            ->where('partner_id', 3)
//            ->get();
//
//        $service = new PackageService();
//
//        foreach ($tracks as $track) {
//
//            $service->updateStatus($track, $track->status);
//            $this->info("Status sent for Track ID: $track->id");
//
//        }
//
//        $this->info("Finished processing all track IDs.");

//        $ids = [
//            350321984886000,900826777789000,800830127764000,300831501563000,350316695131000,750316259221000,650312617651000,950316632672000,250313679780000,201051461109000,450315310979000,900828379718000,201052270854000,201052270859000,900827767224000,800825106207000,600825314771000,201051532584000,550300955934000,250313458096000,950315033652000,800819325291000,900833138603000,700829775604000,550314589430000,650317210368000,850312939500000,750317853952000,700828135134000,250317624485000,850318032613000,201054920944000,700829946812000,900825381677000,900825381877000,650317533821000,350321349867000,550307822090000,300830446499000,950316506130000,900827060144000,400822441183000,201048104473000,350300944021000,750305829794000,600827621896000
//        ];
//
//        $service = new PackageService();
//
//        foreach ($ids as $id) {
//            $track = Track::where('tracking_code', $id)->first();
//
//            if (!$track) {
//                $this->error("Track not found for ID: $id");
//                continue;
//            }
//
//            $service->updateStatus($track, $track->status);
//            $this->info("Status sent for Track ID: $id");
//        }
//
//        $this->info("Finished processing all track IDs.");



//
//        exit();

//        $twoWeeksAgo = Carbon::now()->subWeeks(2);

//        $tracks = Track::where('status', 17)->whereIn('tracking_code', ['500831717190000','950321394949000','750318710134000','850318992252000','750319671392000','350320384731000','950320039176000','350319915475000','900831950788000','500832279847000','201055299620000','400829106133000','350320568244000','300835192391000','250318690289000','350319915476000','400825583004000','700825590607000','850314433580000','300831314213000','800826934580000','550317106838000','300831094799000','300833457806000','550317399555000','300831314214000','950314119061000','450315945112000','250315648537000','201049065767000','950318016069000','600828114082000','750309944703000','350316128910000','350315171751000','201052016518000','600829246199000','600829246909000','300831368960000','400825569150000','250315477265000','950315690765000','500827252183000','850313651195000','350315844570000','900826730558000','300830038682000','950315010334000','750312467620000','350314678055000','201048633680000','600825093926000','700820585160000','900817269558000','500818465610000'
//        ])
//            ->get();
//
////        $tracks = Track::where('status', 17)
////            ->whereBetween('created_at', ['2025-05-20 00:00:00', '2025-05-31 23:59:59'])
////            ->get();
//
//        foreach ($tracks as  $track){
//            $this->line($track->id. ' tehvil verildi statusu yeniden gonderildi');
//            (new PackageService())->updateStatus($track, 17);
//        }
//        dd($tracks);
//        die();
//        $tracks = Track::where('tracking_code','700785058743000')->first();
//        $data = $this->get_goods_noid($tracks->goods);
//        dd($data);
//        die();
//        $packages = Package::with(["user","user.delivery_point","user.delivery_point"])->where('id',325323)->get();
//        foreach ($packages as $package){
//            $this->info($package->custom_id);
//            $user = $package->user;
//            dd($user->delivery_point->work_time);
////            dd($user->delivery_point);
////            dd($package->delivery_point->work_time);
////            $filial_work_time=$user->delivery_point->work_time;
////            dd($filial_work_time);
//            Notification::sendPackage($package->id, 2);
//        }
//        dd('bitdi');
//        die();
//    //dd($tracks->count());
//    foreach ($tracks as $track){
//
////        $sendWpAboutPayment = $this->sendTrack($track->id,5);
////        echo $sendWpAboutPayment;
//
//        $text="OZON saytından $track->tracking_code izləmə nömrəli bağlamanız sistemə daxil olmuşdur. Xahiş edirik ki, elektron gömrük portalına (Smart Customs) əlavə olunmuş bəyannaməni *bu gün ərzində* təcili doldurub təsdiqləyəsiniz.
//        *Diqqət!*
//        -*Bəyannamə Smart-ın “Bəyan edilməmişlər” bölməsində çıxmadısa, zəhmət olmasa FİN-kod/pasport nömrəsi məlumatını qeyd edin!*
//        - Bəyannamədəki izləmə nömrəsinin hansı məhsula aid olduğunu əks olunan invoys dəyərindən və ya OZON saytından təyin edə bilərsiniz.
//        - Dəyər KZT-USD çevirinə uyğun Dollarla qeyd edilməlidir.
//
//        *Bəyannamə mümkün qədər tez daxil edilməlidir, əks halda bağlamanız gömrük tərəfindən ödənişli olaraq saxlanılacaq!*
//
//        Əlaqə nömrəsi: +994 50 256 0075";
//
//        $queue = [
//            'type' => 'TRACK',
//            'user_id' => $track->user->id,
//        ];
//
//        $content = json_encode([
//            'whatsapp' => $text,
//            'sms' => "OZON-dan $track->tracking_code baglamaniz sisteme daxil olmushdur. Beyannameni Smart Customs-da tecili beyan edin, eks halda baglamaniz gomrukde qalacaq
//            +994502560075
//            Hormetle, ASE Shop"
//        ]);
//
//        NotificationQueue::create([
//            'user_id' => $track->user->id,
//            'type' => 'WHATSAPP',
//            'to' => $track->user->phone,
//            'sent' => 0,
//            'content' => $content,
//        ]);
//
////        $data = $this->sendWhatsappMessage($track->user->phone, $text, (object)$queue);
//    }
//        dd('bitdi qaqasim');
//        return;
//	 $this->resetOzon2();
//	 return;
//         $track = Track::where('tracking_code','GE2411216004103')->first();
//	 $track->carrierReset();
//	 return;
// //        $shippingAmount = $package->getShippingAmountUSD();
// //        if (!$shippingAmount)
// //             $shippingAmount = 0;
////	 $cm->invoyS_PRICE = $shippingAmount;
////         $cm->trackingNumber = $package->custom_id;
//// 	 $track = Track::where('tracking_code','200740659378000')->first();
//	 $cm->airwaybill=NULL;
//	 $cm->depesH_NUMBER=NULL;
//         if($track->container && $track->container->name) {
//            $cm->airwaybill = $track->container->name;
//         }
//         if($track->airbox && $track->airbox->name) {
//            $cm->depesH_NUMBER = $track->airbox->name;
//	 }
//	 $cm->quantitY_OF_GOODS=1;
//         $cm->trackingNumber = $track->tracking_code;
//	 $cm->weighT_GOODS = $track->weight;
//	 $cm->get_goods_noid($track->goods);
//         $cpost = $cm->weight_carriers();


    }

//    function get_goods_noid($items)
//    {
//        $this->goods_idList = [];
//        $this->name_of_goodsList = [];
//        foreach ($items as $item) {
//            if ($item->ru_type_id && $item->ru_type && $item->ru_type->name_ru) {
//                $this->goods_idList[] = 0;
//                $this->name_of_goodsList[] = $item->ru_type->name_ru;
//                dd($item->ru_type->name_ru);
//            }
//        }
//        if (count($this->goods_idList) <= 0) {
//            $this->goods_idList[] = 0;
//            $this->name_of_goodsList[] = '-';
//        }
//    }
//
//    public function sendTrack($trackId, $status)
//    {
//        $track = Track::find($trackId);
//
//        if (!$track) {
//            return false;
//        }
//        if ($track->partner_id == 8 && !$track->container_id && in_array($status, [16, 20]) && !$track->scan_no_check) { //GFS no MAWB and inBaku/inKobia
//            return false;
//        }
//
//        $track_filial_name = null;
//        $track_filial_address = null;
//        $track_filial_contact_name = null;
//        $track_filial_contact_phone = null;
//        $track_filial_url = null;
//        $track_filial_work_time = null;
//        $filial = $track->filial;
//        if ($filial) {
//            $track_filial_work_time = $filial->work_time;
//        }
//        if ($track->azerpost_office) {
//            if ($track->azerpost_office->description) {
//                $track_filial_name = $track->azerpost_office->description;
//            }
//            if ($track->azerpost_office->address) {
//                $track_filial_address = $track->azerpost_office->address;
//            }
//            if (isset($track->azerpost_office->contact_phone) && $track->azerpost_office->contact_phone) {
//                $track_filial_contact_phone = $track->azerpost_office->contact_phone;
//            }
//            if (isset($track->azerpost_office->contact_name) && $track->azerpost_office->contact_name) {
//                $track_filial_contact_name = $track->azerpost_office->contact_name;
//            }
//            $track_filial_url = locationUrl($track->azerpost_office->latitude, $track->azerpost_office->longitude);
//        } else if ($track->azeriexpress_office) {
//            if ($track->azeriexpress_office->description) {
//                $track_filial_name = $track->azeriexpress_office->description;
//            }
//            if ($track->azeriexpress_office->address) {
//                $track_filial_address = $track->azeriexpress_office->address;
//            }
//            if (isset($track->azeriexpress_office->contact_phone) && $track->azeriexpress_office->contact_phone) {
//                $track_filial_contact_phone = $track->azeriexpress_office->contact_phone;
//            }
//            if (isset($track->azeriexpress_office->contact_name) && $track->azeriexpress_office->contact_name) {
//                $track_filial_contact_name = $track->azeriexpress_office->contact_name;
//            }
//            $track_filial_url = locationUrl($track->azeriexpress_office->latitude, $track->azeriexpress_office->longitude);
//        } else if ($track->surat_office) {
//            if ($track->surat_office->description) {
//                $track_filial_name = $track->surat_office->description;
//            }
//            if ($track->surat_office->address) {
//                $track_filial_address = $track->surat_office->address;
//            }
//            if (isset($track->surat_office->contact_phone) && $track->surat_office->contact_phone) {
//                $track_filial_contact_phone = $track->surat_office->contact_phone;
//            }
//            if (isset($track->surat_office->contact_name) && $track->surat_office->contact_name) {
//                $track_filial_contact_name = $track->surat_office->contact_name;
//            }
//            $track_filial_url = locationUrl($track->surat_office->latitude, $track->surat_office->longitude);
//        } else if ($track->delivery_point /*&& $track->store_status != 2*/) {
//            if ($track->delivery_point->description) {
//                $track_filial_name = $track->delivery_point->description;
//            }
//            if ($track->delivery_point->address) {
//                $track_filial_address = $track->delivery_point->address;
//            }
//            if (isset($track->delivery_point->contact_phone) && $track->delivery_point->contact_phone) {
//                $track_filial_contact_phone = $track->delivery_point->contact_phone;
//            }
//            if (isset($track->delivery_point->contact_name) && $track->delivery_point->contact_name) {
//                $track_filial_contact_name = $track->delivery_point->contact_name;
//            }
//            $track_filial_url = locationUrl($track->delivery_point->latitude, $track->delivery_point->longitude);
//        }
//
//
//        $data = [
//            'id' => $track->id,
//            'cwb' => $track->tracking_code,
//            'user' => $track->fullname,
//            'city' => $track->city_name,
//            'price' => $track->delivery_price_with_label,
//            'weight' => $track->weight,
//            'label_pdf' => str_replace('admin.', '', route('track_label', $track->tracking_code)),
//            'fin_url' => str_replace('admin.', '', route('track-fin', $track->custom_id)),
//            'pay_url' => str_replace('admin.', '', route('track-pay', $track->custom_id)),
//            'paid' => $track->paid,
//            'track_filial_name' => $track_filial_name,
//            'track_filial_address' => $track_filial_address,
//            'track_filial_contact_name' => $track_filial_contact_name,
//            'track_filial_contact_phone' => $track_filial_contact_phone,
//            'track_filial_url' => $track_filial_url,
//            'track_filial_work_time' => $track_filial_work_time,
//        ];
//
//        $template = ($status == 'package_not_paid' || $status == 'transit_filial_added' || $status == 'track_scan_diff_price') ? $status : ('track_status_' . $status);
//        $template1 = 'track_status_' . $status . '_' . $track->partner_id;
////        return env('SMS_NOTIFICATION') ? SMS::sendByTrack($track, $data, $template) : false;
//        return env('SAAS_ACTIVE') ? Whatsapp::sendByTrack($track, $data, $template, $template1) : false;
//    }
//
//
//    public function sendWhatsappMessage($number, $text, $queue = null)
//    {
//        try {
//            $extraMessage = $queue->type == 'TRACK';
//            $user = $extraMessage ? Customer::find($queue->user_id) : User::find($queue->user_id);
//            $fullname = $user->name . ' ' . $user->surname;
//
//            $number = SMS::clearNumber($number);
//
//
//            $postfields = [
//                'message' => $text,
//                'phone_number' => $number,
//                'full_name' => $fullname ?? null,
//            ];
//            $curl = curl_init();
//
//            curl_setopt_array($curl, array(
//                CURLOPT_URL => 'https://mesagy.com/api/v1/whatsapp/send-message',
//                CURLOPT_RETURNTRANSFER => true,
//                CURLOPT_ENCODING => '',
//                CURLOPT_MAXREDIRS => 10,
//                CURLOPT_TIMEOUT => 0,
//                CURLOPT_FOLLOWLOCATION => true,
//                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//                CURLOPT_CUSTOMREQUEST => 'POST',
//                CURLOPT_POSTFIELDS => $postfields,
//                CURLOPT_HTTPHEADER => array(
//                    'Authorization: msI!1MP2yz3TAAlmMOeQmGiR1IPPclQU',
//                    'Content-Type: multipart/form-data'
//                ),
//            ));
//
//            $response = json_decode(curl_exec($curl));
//            curl_close($curl);
//
//            if ($response->status == "SENT" || $response->status == "DUPLICATE_SEND_ATTEMPT") {
//                return ['success' => true, 'message' => 'WP sent to customer'];
//            } else {
//                echo('getmedi ' . $queue->user_id . " $number " . $response->status);
////                throw new Exception($response->errors->message ?? 'Unknown error');
//            }
//
//
//        } catch (Exception $e) {
//            return ['success' => false, 'error' => $e->getMessage()];
//        }
//    }
//
//    private function getExtraMessageContent()
//    {
//        return '
//            Ozon
//            +994 50 256 00 75
//
//            Iherb
//            +994 51 205 46 21
//
//            UK/USA Ase shop
//            +994 50 286 90 94
//
//            TR/GE Ase shop
//            +994 51 250 08 10
//
//            Taobao
//            +994 10 232 72 06';
//    }
//
//    public function updateFilials()
//    {
//        $items = Track::where("azerpost_office_id", 1796)->where('tracks.status', '!=', 17)->whereNull('tracks.deleted_at');
//        $items = $items->get();
//        echo count($items) . "\n";
//        $num = 0;
//        foreach ($items as $track) {
//            $num++;
//            echo $num . " " . $track->tracking_code . " " . $track->created_at . " \n";
//            $track->azerpost_office_id = 1786;
//            $track->bot_comment = 'Filial from AZ1134 to AZ1102';
//            $track->save();
//        }
//    }
//
//    public function updateDetailedTypes()
//    {
//        $items = Track::whereRaw("(created_at>'2024-10-15 00:00:00')")->where('tracks.status', '!=', 17)->whereNull('tracks.deleted_at');
//        $items = $items->select('tracks.*');
//        $items = $items->orderBy('id', 'asc');
//        $items = $items->get();
//        echo count($items) . "\n";
//        $num = 0;
//        foreach ($items as $track) {
//            $num++;
//            //$track->bot_comment='smart_reset';
//            //$track->save();
//            if (!$track->goods) continue;
//            if (count($track->goods) <= 0) continue;
//            $new_detailed_type = '';
//            foreach ($track->goods as $good) {
//                if (!empty($new_detailed_type))
//                    $new_detailed_type .= '; ';
//                $new_detailed_type .= $good->number_items . ' x ' . $good->ru_type->name_ru;
//            }
//            if ($new_detailed_type == $track->detailed_type) continue;
//            echo $num . " " . $track->tracking_code . " " . $track->created_at . " " . $track->detailed_type . "\n";
//            echo "     " . $track->detailed_type . "\n";
//            echo "     " . $new_detailed_type . "\n";
//            $track->detailed_type_bak = $track->detailed_type;
//            $track->detailed_type = $new_detailed_type;
//            $track->save();
//            //echo "  Ok\n";
//        }
//    }
//
//    public function resetOzon2()
//    {
//        $tfins = [
//            ['450076294789000', '4K0L6WM']
//            , ['400586845402000', '43B5F4']
//            , ['500585083033000', '29K9NRT']
//            , ['800589240999000', '278W687']
//            , ['950074127409000', '36YHY8G']
//            , ['450074633205000', '1NKX778']
//            , ['550069728754000', '8281ZG0']
//            , ['250072333538000', '5SSZVKX']
//            , ['650072797350000', '1P1A0VK']
//            , ['900583271037000', '2532KPB']
//            , ['900587552165000', '1192EK1']
//            , ['500586312244000', '1PBE471']
//            , ['750073061074000', '11SEQ3A']
//            , ['700586351979000', '6F0NPVN']
//            , ['600586181079000', '7V16Y3Z']
//            , ['950070419524000', '0WD5EFE']
//            , ['450075354732000', '1ZSBTT4']
//            , ['450074633203000', '1NKX778']
//            , ['900584093987000', '2MXPC39']
//            , ['550068194801000', '6J81D26']
//            , ['700590175163000', '881RSP2']
//            , ['900588682655000', '167WE7S']
//            , ['700589398237000', '2MMBXY2']
//            , ['400584756255000', '00BKT41']
//            , ['250072366773000', '17S8G72']
//            , ['900584835226000', '6ZN873Y']
//            , ['250073171746000', '112375']
//            , ['300584915116000', '6GMDP6Z']
//            , ['500587998728000', '7TCRXK2']
//            , ['350074519469000', '1MJ2U1G']
//            , ['300587270869000', '1Y7DJJR']
//            , ['250075814044000', '1CQQEKR']
//            , ['600590223762000', '2CLGEC2']
//            , ['550072491210000', '62G9EH3']
//            , ['400586845403000', '43B5F4']
//            , ['450076244375000', '1G2QCXH']
//            , ['750069036340000', '168LB8S']
//            , ['350070755536000', '5MYW21W']
//            , ['500583710751000', '0Y5V8D4']
//            , ['350075541411000', '3KRCJDP']
//            , ['800587059632000', '45F1BD']
//            , ['300587025123000', '2GKT0BU']
//            , ['550071809309000', '0XE1D29']
//            , ['550069728755000', '8281ZG0']
//            , ['350073935166000', '5PA8VNM']
//            , ['600586122248000', '1K1K622']
//            , ['600587086109000', '2CFVJB4']
//            , ['300590132302000', '25VHSHT']
//            , ['750070812066000', '2UDLGWT']
//            , ['400582231544000', '5UZ6UV2']
//            , ['950073099346000', '4W3WYNR']
//            , ['750074022385000', '5JCQYMD']
//            , ['950072567409000', '6764713']
//            , ['650076258883000', '3H4TMVU']
//            , ['500583093385000', '5UDELHY']
//            , ['200812692700000', '11LB0Y7']
//            , ['350075634619000', '44WJKT3']
//            , ['550073985066000', '2SGP9C6']
//            , ['550071559180000', '1JH7TDA']
//            , ['700589185955000', '59E429N']
//            , ['750074022386000', '5JCQYMD']
//            , ['750072592838000', '2UK58R8']
//            , ['950074127411000', '36YHY8G']
//            , ['200812728576000', '7WLGKYA']
//            , ['300587339235000', '5EL6VQC']
//            , ['750075212760000', '4Z6C6QB']
//            , ['450072881544000', '111HKBB']
//            , ['350075307408000', '403KUTS']
//            , ['650071730368000', '632S209']
//            , ['800584388560000', '4YF9DSR']
//            , ['350072544897000', '18A4S5D']
//            , ['550069728752000', '8281ZG0']
//            , ['700588616359000', '1JY1HPZ']
//            , ['950073903692000', '1G9HW0B']
//            , ['700588616377000', '1JY1HPZ']
//            , ['350075634618000', '44WJKT3']
//            , ['950070015942000', '1NYYR0N']
//            , ['750071689430000', '3C66C9']
//            , ['550074685630000', 'F5A72']
//            , ['850071312079000', '1BE175']
//            , ['550069547396000', '109BE3']
//            , ['600588702591000', '0WSWE60']
//            , ['200812692699000', '11LB0Y7']
//            , ['250076141530000', '1MSTG6H']
//            , ['500587998723000', '7TCRXK2']
//            , ['900588801909000', '7D6NWBQ']
//            , ['700586351984000', '6F0NPVN']
//            , ['800585198613000', '1K1K622']
//            , ['350076072865000', '1192EK1']
//            , ['750074225482000', '2UDLGWT']
//            , ['250072115300000', '28W3D2U']
//            , ['700585417291000', '515GS39']
//            , ['350072218720000', '2FUDHAK']
//            , ['250076141527000', '1MSTG6H']
//            , ['450071686692000', '0XQ7092']
//            , ['250074413362000', '1J6PDHR']
//            , ['850074614748000', '0W6ZT64']
//            , ['950074127410000', '36YHY8G']
//            , ['700588035627000', '55Z8Y75']
//            , ['850071312077000', '1BE175']
//            , ['350072544898000', '18A4S5D']
//            , ['850069708471000', '4HBQ3HP']
//            , ['550069547400000', '109BE3']
//            , ['850071507756000', '4YF9DSR']
//            , ['500583672262000', '1K1K622']
//            , ['550068194933000', '6J81D26']
//            , ['550071345854000', '7BXH5PX']
//            , ['950072211379000', '22A90D7']
//            , ['800586300949000', '7C2HW2C']
//            , ['950070259230000', '5TPSW3Z']
//            , ['700590221018000', '64TYTFN']
//            , ['400585655952000', '6A8KC31']
//            , ['950071216029000', '2ZB9C5K']
//            , ['350073721751000', '8FGJDXA']
//            , ['550074238441000', '67MY55X']
//            , ['550071809318000', '0XE1D29']
//            , ['200810525617000', '45F1BD']
//            , ['300587933560000', '322S1YQ']
//            , ['800585215887000', '17NS1JK']
//            , ['400585206214000', '23C1DNH']
//            , ['300588059893000', '6P557E4']
//            , ['750070812073000', '2UDLGWT']
//            , ['900588801908000', '7D6NWBQ']
//            , ['300587871223000', '2BSJZ3N']
//            , ['900587812330000', '1MFQLMD']
//            , ['800586177024000', '5N89ECW']
//            , ['550072245302000', '5UVJB0F']
//            , ['250076567151000', '0XDVIYS']
//            , ['350073424050000', '1C07379']
//            , ['350074908884000', '0YXQLAK']
//            , ['500586784053000', '1FR2T2G']
//            , ['650071974749000', '6BL1P8X']
//            , ['900586622831000', '60Z7L50']
//            , ['950070214319000', '1F2G6HN']
//            , ['400582356786000', '5UDELHY']
//            , ['450075355965000', '4NEMVQ6']
//            , ['550071809319000', '0XE1D29']
//            , ['350073418283000', '2GCE4UG']
//            , ['700587084709000', '3C66C9']
//            , ['850075297746000', '5LLQSFB']
//            , ['700590393395000', '1FN5ZVG']
//            , ['700590946007000', '137QT8S']
//            , ['450072881546000', '111HKBB']
//            , ['650074596592000', '5PH357D']
//            , ['900587870479000', '4TAEHVJ']
//            , ['750070812071000', '2UDLGWT']
//            , ['850069746720000', '6JYU5D7']
//            , ['300586636361000', '191L2PP']
//            , ['850075093558000', '1HZYB68']
//            , ['550070099226000', '11VUAK3']
//            , ['250072223712000', '881GU2B']
//            , ['650071361106000', '5MYW21W']
//            , ['950071853064000', '4RK5YMZ']
//            , ['800585987750000', '0TRHE73']
//            , ['900585310689000', '51CBWGH']
//            , ['250076981793000', '1172HCA']
//            , ['450070908571000', '4U7XER0']
//            , ['800584757450000', '6CRFKQB']
//            , ['850069746723000', '6JYU5D7']
//            , ['300585087369000', '5NCYX2U']
//            , ['550070700681000', '72JN7WL']
//            , ['750072592835000', '2UK58R8']
//            , ['700585552251000', '58WR24Q']
//            , ['450075127300000', '5L4Q9LU']
//            , ['950075156542000', '110YWTN']
//            , ['450071126842000', '1QTDG4P']
//            , ['200811637381000', '1GB3CU7']
//            , ['800589284210000', '6EQ2JS0']
//            , ['300587324757000', '1MJ2U1G']
//            , ['250072333537000', '5SSZVKX']
//            , ['650072797352000', '1P1A0VK']
//            , ['650071928044000', '61B38KD']
//            , ['400583512084000', '4QFHURS']
//            , ['950074674468000', '1LJGUK9']
//            , ['400585940035000', '735XS4E']
//            , ['300587329226000', '2DLJ35G']
//            , ['800589234108000', '14WZZDF']
//            , ['700587084719000', '3C66C9']
//            , ['700589590123000', '6VW7AVY']
//            , ['450072698776000', '19UEXWT']
//            , ['350076882993000', '43F21D']
//            , ['550071353458000', '0Y3C3F6']
//            , ['650072927295000', '1B76P2G']
//            , ['400581573199000', '4MFTMU6']
//            , ['900586012227000', '45F1BD']
//            , ['250076141529000', '1MSTG6H']
//            , ['800585184128000', '430B3F']
//            , ['400581956360000', '5RVVE82']
//            , ['500583093382000', '5UDELHY']
//            , ['650074596605000', '5PH357D']
//            , ['200810100879000', '112375']
//            , ['800586808102000', '5WURF91']
//            , ['300584901669000', '18A4S5D']
//            , ['450071181353000', '2N3PUVE']
//            , ['750070773083000', '7VNKJCF']
//            , ['300585087368000', '5NCYX2U']
//            , ['650071073282000', '1RFEKQ0']
//            , ['850070565188000', '1PKGN9F']
//            , ['250074613107000', '60XMX4H']
//            , ['850070565187000', '1PKGN9F']
//            , ['750071696542000', '37BT4SG']
//            , ['700590114057000', '0ZW44JX']
//            , ['800587059629000', '45F1BD']
//            , ['250073863135000', '4RZ65RX']
//            , ['800588700682000', '5WYY3G8']
//            , ['400585338246000', '764MP3M']
//            , ['500586784052000', '1FR2T2G']
//            , ['650076573595000', '0XSUK1J']
//            , ['400586456380000', '2S0K1C6']
//            , ['300589314248000', '5XSRPMG']
//            , ['450076167643000', '4QVZ4RU']
//            , ['900588365522000', '39KRVTH']
//            , ['350074908883000', '0YXQLAK']
//            , ['400585206213000', '23C1DNH']
//            , ['350073787145000', '4WU7EP7']
//            , ['500585363090000', '10D5ZVY']
//            , ['650076521484000', '1LC4H4E']
//            , ['250072366775000', '17S8G72']
//            , ['850072873569000', '764MP3M']
//            , ['350073008719000', '0ZY6V9S']
//            , ['400585161433000', '67JFZJD']
//            , ['800589284211000', '6EQ2JS0']
//            , ['400586030959000', '4TAEHVJ']
//            , ['250072017550000', '1AQ34JL']
//            , ['500588068646000', '26GN55T']
//            , ['550071559181000', '1JH7TDA']
//            , ['450074633200000', '1NKX778']
//            , ['450076298994000', '0WSWE60']
//            , ['800589802569000', '1CU8MQ6']
//            , ['550068194824000', '6J81D26']
//            , ['350073639289000', '2TJA1R0']
//            , ['500586737881000', '55ZJ1UN']
//            , ['800586808100000', '5WURF91']
//            , ['800588700684000', '5WYY3G8']
//            , ['700588616371000', '1JY1HPZ']
//            , ['500583715867000', '6BL1P8X']
//            , ['350074061131000', '26T9FGZ']
//            , ['250075133476000', '21CQGJR']
//            , ['450074633201000', '1NKX778']
//            , ['800585184124000', '430B3F']
//            , ['500588068648000', '26GN55T']
//            , ['950070214313000', '1F2G6HN']
//            , ['350073008721000', '0ZY6V9S']
//            , ['500584919086000', '2056NE7']
//            , ['850071549281000', '37BT4SG']
//            , ['400587474995000', '2CLGEC2']
//            , ['800584731483000', '7GGCN9Z']
//            , ['450075285183000', '8ZWYWUK']
//            , ['450075127297000', '5L4Q9LU']
//            , ['850070637097000', '112375']
//            , ['550069936545000', '4LL52B5']
//            , ['200810577740000', '60Z7L50']
//            , ['250073442805000', '1Z4X60R']
//            , ['400583871739000', '55MAPW0']
//            , ['550071020763000', '2TJA1R0']
//            , ['350075541413000', '3KRCJDP']
//            , ['400586030964000', '4TAEHVJ']
//            , ['950075302729000', '7Q4Z24W']
//            , ['200810577738000', '60Z7L50']
//            , ['650074240109000', '5NNGDC2']
//            , ['850070913711000', '237W911']
//            , ['700585992320000', '1WGE1WL']
//            , ['900587267852000', '0YQEE2A']
//            , ['800589240994000', '278W687']
//            , ['700585759873000', '63NF7Q5']
//            , ['550071809311000', '0XE1D29']
//            , ['500584310837000', '4C59H6D']
//            , ['350072144352000', '2NRJCCD']
//            , ['350076251292000', '5NKCF2F']
//            , ['300585639181000', '28BQ3D8']
//            , ['350077235417000', '1X16YPZ']
//            , ['800588700685000', '5WYY3G8']
//            , ['900587372053000', '5BALGKJ']
//            , ['500583253238000', '138JYXS']
//            , ['850075044186000', '728GQT5']
//            , ['750074042602000', '6BL1P8X']
//            , ['600586962738000', '4ZQS15E']
//            , ['350075789083000', '20ZQS0D']
//            , ['350072710996000', '1BE175']
//            , ['650074218455000', '00N64GA']
//            , ['500586604207000', '3KRCJDP']
//            , ['750073061072000', '11SEQ3A']
//            , ['500584974464000', '1D0SYPQ']
//            , ['200809423696000', '3KDQ8KP']
//            , ['900584042088000', '3J9771C']
//            , ['800588485731000', '13JESTY']
//            , ['850068595106000', '6Y24AE6']
//            , ['450075278540000', '27PQSEJ']
//            , ['900585553651000', '0TMQELC']
//            , ['200811629492000', '4YF9DSR']
//            , ['500584310836000', '4C59H6D']
//            , ['350072493228000', '2FBDVN4']
//            , ['750070637769000', '2FBDVN4']
//            , ['450075354593000', '1ZSBTT4']
//            , ['650074596603000', '5PH357D']
//            , ['200811029138000', '4NB59JR']
//            , ['300584977613000', '1ZT45UE']
//            , ['950075156543000', '110YWTN']
//            , ['950071792609000', '00W3GFD']
//            , ['200809773029000', '1BVJ61V']
//            , ['550069994991000', '632S209']
//            , ['300588997771000', '4YF9DSR']
//            , ['350070755534000', '5MYW21W']
//            , ['200810525616000', '45F1BD']
//            , ['500582599432000', '67LVD65']
//            , ['600584381204000', '6ABDN7G']
//            , ['900588744622000', '8AS8P39']
//            , ['200808039898000', '2U0D562']
//            , ['750071639768000', '1AF5X74']
//            , ['650073806519000', '5ULWPSW']
//            , ['700587334345000', '82VATKW']
//            , ['450073673813000', '4M141KZ']
//            , ['850072599974000', '1N8KZM5']
//            , ['350075797085000', '5LPS2DC']
//            , ['450074633206000', '1NKX778']
//            , ['950070450903000', '2S2RJ9M']
//            , ['600588809099000', '1CWSWMT']
//            , ['450074943199000', '20ZQS0D']
//            , ['400584002755000', '0W3W84Z']
//            , ['750072592837000', '2UK58R8']
//            , ['500584310843000', '4C59H6D']
//            , ['250075856257000', '2TY8BD8']
//            , ['500583674083000', '2ZB9C5K']
//            , ['700588035628000', '55Z8Y75']
//            , ['800589720630000', '365ZEWK']
//            , ['550071083091000', '0WPTY74']
//            , ['750074805924000', '02CRDXJ']
//            , ['300587799225000', '3S60JRF']
//            , ['750071592324000', '64ACUES']
//            , ['200810996116000', '82GXGR7']
//            , ['950072211387000', '22A90D7']
//            , ['600585588546000', '1WGC497']
//            , ['500585937903000', '0WMF5WV']
//            , ['900585112850000', '16FN5AD']
//            , ['350070755535000', '5MYW21W']
//            , ['800585215885000', '17NS1JK']
//            , ['500585268775000', '198131']
//            , ['500586801773000', '1ETUY9L']
//            , ['600586918927000', '5LQBPVV']
//            , ['800589335101000', '39KRVTH']
//            , ['750071889792000', '1AF5X74']
//            , ['700588616375000', '1JY1HPZ']
//            , ['650074596594000', '5PH357D']
//            , ['450074762301000', '2FXSB5R']
//            , ['200809169546000', '1YULCXZ']
//            , ['900587870480000', '4TAEHVJ']
//            , ['950071216023000', '2ZB9C5K']
//            , ['950073099348000', '4W3WYNR']
//            , ['600586559030000', '6ADRCFG']
//            , ['250076141524000', '1MSTG6H']
//            , ['700585911761000', '3Z3B9TV']
//            , ['500583326488000', '201976']
//            , ['350070755528000', '5MYW21W']
//            , ['950069800065000', '241Q5BG']
//            , ['500587957682000', '1UXKGGK']
//            , ['250076640022000', '21WYBVW']
//            , ['350076419382000', '2RH4GLE']
//            , ['600585188592000', '5KTN69P']
//            , ['350076970012000', '2R9CRHT']
//            , ['550071959831000', '2NG3XAQ']
//            , ['500586528282000', '115Z00U']
//            , ['750074381773000', '564U98U']
//            , ['850071530777000', '1TAGK0M']
//            , ['250073553610000', '20LCK5D']
//            , ['700589692611000', '8L5E8AR']
//            , ['200811629491000', '4YF9DSR']
//            , ['300587270862000', '1Y7DJJR']
//            , ['750072592836000', '2UK58R8']
//            , ['250074314131000', '0WEXGBH']
//            , ['950071792604000', '00W3GFD']
//            , ['550069960088000', '2NG3XAQ']
//            , ['350072144351000', '2NRJCCD']
//            , ['900587267855000', '0YQEE2A']
//            , ['300586945265000', '105Y0RS']
//            , ['800589460301000', '4NEMVQ6']
//            , ['800586040320000', '6LL3JHR']
//            , ['900585112848000', '16FN5AD']
//            , ['700587332741000', '82VATKW']
//            , ['750070812074000', '2UDLGWT']
//            , ['350076704609000', '39HC7SC']
//            , ['250072353466000', '2SJUE28']
//            , ['650071638728000', '1UC5A20']
//            , ['900588365520000', '39KRVTH']
//            , ['450071126844000', '1QTDG4P']
//            , ['600589216060000', '1WVBQE5']
//            , ['500584603443000', '1UC5A20']
//            , ['950072805883000', '2G1PNXG']
//            , ['250073442944000', '7GGCN9Z']
//            , ['400586116110000', '1RD0LHK']
//            , ['800589284212000', '6EQ2JS0']
//            , ['350076842950000', '8RD5E11']
//            , ['900583879929000', '4NB59JR']
//            , ['350070756600000', '5MYW21W']
//            , ['400580945191000', '0VDB3UN']
//            , ['750074381756000', '564U98U']
//            , ['350077565980000', '0UMN950']
//            , ['650074596600000', '5PH357D']
//            , ['550070069658000', '1QV78K']
//            , ['250075590598000', '18TW4PC']
//            , ['450074633204000', '1NKX778']
//            , ['900588365521000', '39KRVTH']
//            , ['550069547395000', '109BE3']
//            , ['850071369116000', '0TRHE73']
//            , ['750073650696000', '2E8DE5']
//            , ['900584042993000', '3J9771C']
//        ];
//        $num = 0;
//        foreach ($tfins as $tfin) {
//            $num++;
//            $track = Track::where('tracking_code', $tfin[0])->first();
//            if (!$track) continue;
//            if ($track->status != 6) continue;
//            echo $num . " " . $tfin[0] . "   " . $tfin[1] . "\n";
//            /*$customer=$track->customer;
//            $track->fin=$tfin[1];
//            $track->bot_comment="change fin code";
//            $track->save();
//            $customer->fin=$tfin[1];
//            $customer->save();*/
//            $track->carrierReset();
//            //echo "   ".$track->fin."  ".$track->phone."  ".$customer->fin."  ".$customer->phone."\n";
//            echo "    " . $track->tracking_code . " " . $track->status . "\n";//." ".$track->detailed_type."\n";
//        }
//    }
//
//    public function resetOzon()
//    {
//        $items = Track::where("partner_id", 3)->where('tracks.status', 6)->whereNull('tracks.deleted_at')->incustoms(7);
//        //$items = $items->where('tracks.bot_comment','!=','smart_reset');
//        $items = $items->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id');
//        $items = $items->select('tracks.*');
//        $items = $items->where('tracks.fin', '-');
//        $items = $items->whereNotNull('package_carriers.id');
//        $items = $items->orderBy('id', 'asc');
//        $items = $items->get();
//        echo count($items) . "\n";
//        $num = 0;
//        foreach ($items as $track) {
//            $num++;
//            $track->carrierReset(true);
//            /*$track->status=6;
//            $track->bot_comment='Removed from smart (Wrong fin code -)';
//            $track->save();
//            (new PackageService())->updateStatus($track, 6);*/
//            $fin = '';
//            if ($track->customer) $fin = $track->customer->fin;
//            echo $num . " " . $track->tracking_code . " " . $track->carrier->id . " " . $track->fin . " " . $fin . " " . $track->created_at . "\n";//." ".$track->detailed_type."\n";
//        }
//    }
//
//    public function send_diff_price()
//    {
//        $items = Track::whereRaw("(bot_comment='Scanned but Different price' and status=18 and id !=166281 and deleted_at is NULL)");
//        $items = $items->orderBy('id', 'asc');
//        $items = $items->get();
//        echo count($items) . "\n";
//        $num = 0;
//        foreach ($items as $track) {
//            $num++;
//            echo $num . " Sending " . $track->tracking_code . " " . $track->created_at . "\n";
//            Notification::sendTrack($track->id, 'track_scan_diff_price');
//            $track->bot_comment = "Sent Different price Notification";
//            $track->save();
//        }
//    }
//
//    public function clear_couriers()
//    {
//        $items = Track::with(['carrier', 'partner', 'container', 'airbox', 'city', 'courier_delivery']);//::paginate($this->limit);
//        $items = $items->leftJoin('courier_deliveries', 'tracks.courier_delivery_id', 'courier_deliveries.id');
//        $items = $items->leftJoin('couriers', 'courier_deliveries.courier_id', 'couriers.id');
//        $items = $items->select('tracks.*');
//        $items = $items->whereNull('tracks.deleted_at');
//        $items = $items->where('tracks.partner_id', 8);
//        $items = $items->whereNotNull('tracks.courier_delivery_id');
//        $items = $items->whereNull('courier_deliveries.deleted_at');
//        $items = $items->where('courier_deliveries.status', 2);
//        $items = $items->whereIn('couriers.name', ['Jamal', 'Fizuli', 'ASE Ali', 'Karam', 'Eyvaz ASE', 'Nurik', 'Vasif', 'Nicat']);
//        $items = $items->orderBy('couriers.name', 'asc');
//        $items = $items->get();
//        echo count($items) . "\n";
//        $num = 0;
//        foreach ($items as $track) {
//            $cd = $track->courier_delivery;
//            if (!$cd) continue;
//            $num++;
//            echo $num . ' ' . $cd->id . ' ' . $track->id . ' ' . $cd->packages_txt . "  " . $track->status_with_label . "  " . $cd->courier->name . "  " . $cd->status_with_label . "\n";
//            $track->courier_delivery_id = NULL;
//            $track->bot_comment = "Bot clear courier " . $cd->courier->name . " " . $cd->id;
//            $track->save();
//            $cd->delete();
//        }
//    }
//
//
//    public function courier_area_test()
//    {
//        $track = new Track();
//        //$track->city_name='BABA';
//        $track->address = 'NIZeMI 20';
//        $track->city_name = 'baki';
//        $track->partner_id = 8;
//        $courier = CA::areaCourier($track);
//        echo "track: " . $track->partner_with_label . "  " . $track->tracking_code . "  city:" . $track->city_name . "  region: " . $track->region_name . "  address: " . $track->address . "\n";
//        if ($courier) {
//            echo $courier->name . "\n";
//            foreach ($courier->areas as $area) {
//                echo "  " . $area->partner_with_label . " " . $area->type . " " . $area->mach . " " . $area->name . "\n";
//            }
//        }
//    }
//
//    public function ready_packages()
//    {
//        $ready_packages = Package::where('warehouse_id', 4);
//        $ready_packages = $ready_packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE');
//        $ready_packages = $ready_packages->incustoms()->ready()->where('packages.status', 0)->count();
//        echo $ready_packages . "\n";;
//    }
//
//    public function tracks_out_for_delivery()
//    {
//        $items = Track::with(['carrier', 'partner', 'container', 'airbox', 'city', 'courier_delivery']);//::paginate($this->limit);
//        $items = $items->leftJoin('courier_deliveries', 'tracks.courier_delivery_id', 'courier_deliveries.id');
//        $items = $items->select('tracks.*');
//        //$items = $items->whereNotNull('courier_delivery_id');
//        //$items = $items->where('courier_deliveries.courier_id',11);
//        $items = $items->where('tracks.container_id', 242);
//        $items = $items->whereIn('tracks.status', [16, 21]);
//        //$items = $items->where('courier_deliveries.status',1);
//        $items = $items->get();
//        echo count($items) . "\n";
//        $num = 0;
//        foreach ($items as $track) {
//            //if(!$track->courier_delivery || !$track->courier_delivery->courier) {
//            //	continue;
//            //  }
//            $num++;
//            echo $num . ' ' . $track->tracking_code . "  " . $track->status_with_label . "  " . $track->courier_delivery->courier->name . "  " . $track->courier_delivery->status_with_label . "\n";
//            echo "status change";
//            $track->status = 20;
//            $track->bot_comment = "Status from " . $track->status_with_label . " to IN KOBIA";
//            $track->save();
//            (new PackageService())->updateStatus($track, 20);
//            if ($track->courier_delivery && $track->courier_delivery->courier_id == 11) {
//                echo "courier status change";
//                $cd = $track->courier_delivery;
//                $cd->status = 1;
//                $cd->save();
//            }
//            echo "\n";
//            continue;
//        }
//    }
//
//    public function carrier_status()
//    {
//        $items = Track::with(['carrier', 'partner', 'container', 'airbox', 'city', 'courier_delivery']);//::paginate($this->limit);
//        $items = $items->leftJoin('containers', 'tracks.container_id', 'containers.id');
//        $items = $items->leftJoin('airboxes', 'tracks.airbox_id', 'airboxes.id');
//        $items = $items->leftJoin('courier_deliveries', 'tracks.courier_delivery_id', 'courier_deliveries.id');
//        $items = $items->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id');
//        $items = $items->select('tracks.*');
//        $items = $items->where('tracks.status', 7);
//        $items = $items->where('containers.name', '50116344300');
//        $items = $items->whereNotNull('package_carriers.depesH_NUMBER');
//        $items = $items->get();
//        echo count($items) . "\n";
//        foreach ($items as $track) {
//            $track->status = 14;
//            $track->save();
//            (new PackageService())->updateStatus($track, 14);
//        }
//    }
}
