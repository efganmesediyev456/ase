<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\CustomsType;
use App\Models\Discount;
use App\Models\Extra\Whatsapp;
use App\Models\NotificationQueue;
use App\Models\Package;
use App\Models\PackageType;
use App\Models\Track;
use App\Models\Transaction;
use App\Models\UkrExpressModel;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\YeniPoct\YenipoctOrder;
use App\Models\YeniPoct\YenipoctPackage;
use App\Services\KapitalBank\KapitalBankTxpgService;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Console\Command;
use App\Services\Integration\UnitradeService;
use App\Models\Extra\Notification;
use Carbon\Carbon;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use Illuminate\Http\Request;
use Auth;


class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {--cwb=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add carriers from tracks';

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
     * @return mixed
     */

    public static function sendTrack($trackId, $status, $scheduled_at = null)
    {
        $track = Track::find($trackId);
        $track->paid = 0;

        if (!$track) {
            return false;
        }
        if ($track->partner_id == 8 && !$track->container_id && in_array($status, [16, 20]) && !$track->scan_no_check) {
            //GFS no MAWB and inBaku/inKobia
            return false;
        }

        $track_filial_name = null;
        $track_filial_address = null;
        $track_filial_contact_name = null;
        $track_filial_contact_phone = null;
        $track_filial_url = null;
        $track_filial_work_time = null;
        $track_filial_lunch_time = null;
        $filial = $track->filial;
        if ($filial) {
            $track_filial_work_time = $filial->work_time;
            $track_filial_lunch_time = $filial->lunch_time;
        }
        if ($track->azerpost_office) {
            if ($track->azerpost_office->description) {
                $track_filial_name = $track->azerpost_office->description;
            }
            if ($track->azerpost_office->address) {
                $track_filial_address = $track->azerpost_office->address;
            }
            if (isset($track->azerpost_office->contact_phone) && $track->azerpost_office->contact_phone) {
                $track_filial_contact_phone = $track->azerpost_office->contact_phone;
            }
            if (isset($track->azerpost_office->contact_name) && $track->azerpost_office->contact_name) {
                $track_filial_contact_name = $track->azerpost_office->contact_name;
            }
            $track_filial_url = locationUrl($track->azerpost_office->latitude, $track->azerpost_office->longitude);
        } else if ($track->azeriexpress_office) {
            if ($track->azeriexpress_office->description) {
                $track_filial_name = $track->azeriexpress_office->description;
            }
            if ($track->azeriexpress_office->address) {
                $track_filial_address = $track->azeriexpress_office->address;
            }
            if (isset($track->azeriexpress_office->contact_phone) && $track->azeriexpress_office->contact_phone) {
                $track_filial_contact_phone = $track->azeriexpress_office->contact_phone;
            }
            if (isset($track->azeriexpress_office->contact_name) && $track->azeriexpress_office->contact_name) {
                $track_filial_contact_name = $track->azeriexpress_office->contact_name;
            }
            $track_filial_url = locationUrl($track->azeriexpress_office->latitude, $track->azeriexpress_office->longitude);
        } else if ($track->surat_office) {
            if ($track->surat_office->description) {
                $track_filial_name = $track->surat_office->description;
            }
            if ($track->surat_office->address) {
                $track_filial_address = $track->surat_office->address;
            }
            if (isset($track->surat_office->contact_phone) && $track->surat_office->contact_phone) {
                $track_filial_contact_phone = $track->surat_office->contact_phone;
            }
            if (isset($track->surat_office->contact_name) && $track->surat_office->contact_name) {
                $track_filial_contact_name = $track->surat_office->contact_name;
            }
            $track_filial_url = locationUrl($track->surat_office->latitude, $track->surat_office->longitude);
        } else if ($track->yenipoct_office) {
            if ($track->yenipoct_office->description) {
                $track_filial_name = $track->yenipoct_office->description;
            }
            if ($track->yenipoct_office->address) {
                $track_filial_address = $track->yenipoct_office->address;
            }
            if (isset($track->yenipoct_office->contact_phone) && $track->yenipoct_office->contact_phone) {
                $track_filial_contact_phone = $track->yenipoct_office->contact_phone;
            }
            if (isset($track->yenipoct_office->contact_name) && $track->yenipoct_office->contact_name) {
                $track_filial_contact_name = $track->yenipoct_office->contact_name;
            }
            $track_filial_url = locationUrl($track->yenipoct_office->latitude, $track->yenipoct_office->longitude);
        } else if ($track->kargomat_office) {
            if ($track->kargomat_office->description) {
                $track_filial_name = $track->kargomat_office->description;
            }
            if ($track->kargomat_office->address) {
                $track_filial_address = $track->kargomat_office->address;
            }
            if (isset($track->kargomat_office->contact_phone) && $track->kargomat_office->contact_phone) {
                $track_filial_contact_phone = $track->kargomat_office->contact_phone;
            }
            if (isset($track->kargomat_office->contact_name) && $track->kargomat_office->contact_name) {
                $track_filial_contact_name = $track->kargomat_office->contact_name;
            }
            $track_filial_url = locationUrl($track->kargomat_office->latitude, $track->kargomat_office->longitude);
        } else if ($track->delivery_point /*&& $track->store_status != 2*/) {
            if ($track->delivery_point->description) {
                $track_filial_name = $track->delivery_point->description;
            }
            if ($track->delivery_point->address) {
                $track_filial_address = $track->delivery_point->address;
            }
            if (isset($track->delivery_point->contact_phone) && $track->delivery_point->contact_phone) {
                $track_filial_contact_phone = $track->delivery_point->contact_phone;
            }
            if (isset($track->delivery_point->contact_name) && $track->delivery_point->contact_name) {
                $track_filial_contact_name = $track->delivery_point->contact_name;
            }
            $track_filial_url = locationUrl($track->delivery_point->latitude, $track->delivery_point->longitude);
        }

        $data = [
            'id' => $track->id,
            'cwb' => $track->tracking_code,
            'user' => $track->fullname,
            'city' => $track->city_name,
            'price' => $track->delivery_price_with_label,
            'code' => $track->fin ?? $track->customer->fin,
            'weight' => $track->weight,
            'label_pdf' => str_replace('admin.', '', route('track_label', $track->tracking_code)),
            'fin_url' => str_replace('admin.', '', route('track-fin', $track->custom_id)),
            'pay_url' => str_replace('admin.', '', route('track-pay', $track->custom_id)),
            'incustom_url' => str_replace('admin.', '', route('track-pay-debt', $track->custom_id)),
            'broker_url' => str_replace('admin.', '', route('track-pay-broker', $track->custom_id)),
            'broker_fee' => 15,
            'incustom_price' => $track->debt_price,
            'paid' => $track->paid,
            'track_filial_name' => $track_filial_name,
            'track_filial_address' => $track_filial_address,
            'track_filial_contact_name' => $track_filial_contact_name,
            'track_filial_contact_phone' => $track_filial_contact_phone,
            'track_filial_url' => $track_filial_url,
            'track_filial_work_time' => $track_filial_work_time,
            'track_filial_lunch_time' => $track_filial_lunch_time,
        ];

        $template = ($status == 'SCL_Stopped_in_customs_overlimit' || $status == 'package_not_paid' || $status == 'courier_picked_up' || $status == 'Precint_notpaid' || $status == 'customs_storage_fee' || $status == 'OZON_RUS_SMART' || $status == 'tracking_courier_delivery' || $status == 'IHERB_RUS_SMART' || $status == 'TAOBAO_SENT_PAYMENT' || $status == 'TAOBAO_SENT_UNDECLARED' || $status == 'transit_filial_added' || $status == 'PUDO_DELIVERED_STATUS' || $status == 'track_scan_diff_price' || $status == 'customs_broker_fee' ) ? $status : ('track_status_' . $status);

        $template1 = 'track_status_' . $status . '_' . $track->partner_id;



        if ($template1 === 'track_status_20_9' && !$track->paid) {
            return false;
        }

//        if($template == 'customs_broker_fee'){
//            return env('SMS_NOTIFICATION') ? SMS::sendByTrack($track, $data, $template,$template1) : false;
//        }



//        return env('SAAS_ACTIVE') ? Whatsapp::sendByTrack($track, $data, $template, $template1) : false;
//        return env('SAAS_ACTIVE') ? Email::sendByCustomer($track->customer_id, $data, $template, $template1) : false;

    }


    public function sendPackages( $id=237)
    {

        $track = Track::find(726554);
        $this->sendTrack($track->id, 20);
        dd("s");
        $request = new Request();

        $container = YenipoctOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

//        if ($container->status !== AzeriExpressOrder::STATUSES['WAITING']) {
//            return back()->withErrors('Artıq Göndərilmə siyahısına əlavə edilib!');
//        }


        $tracks = YenipoctPackage::with(['container.yeniPoctOffice','track.customer'])
            ->whereIn('status', [
                YenipoctPackage::STATUSES['NOT_SENT'],
                YenipoctPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('yenipoct_order_id', $container->id)
            ->when($request->filled('temu'), function ($query) {
                $query->whereHas('track', function ($subQuery) {
                    $subQuery->where('partner_id', 8);
                });
            }, function ($query) {
                $query->whereHas('track', function ($subQuery) {
                    $subQuery->where('partner_id', '!=', 8);
                });
            })
            ->where('type', 'track')
            ->get();


        $packages = collect();
        if(!$request->filled('temu')){
            $packages = YenipoctPackage::with(['container.yeniPoctOffice', 'package.user', 'track.customer'])
                ->whereIn('status', [
                    YenipoctPackage::STATUSES['NOT_SENT'],
                    YenipoctPackage::STATUSES['HAS_PROBLEM'],
                ])
                ->where('yenipoct_order_id', $container->id)
                ->where('type', 'package')
                ->get();
        }

        $packages = $tracks->merge($packages);
        $assignAdmin = Auth::user()->name ?? null;

        dd($packages->pluck('barcode'));


        foreach ($packages as $package) {
//            $package->company_sent = 1;
//            $package->track->bot_comment = "Bağlama YeniPoct-ə göndərildi".' - '.$assignAdmin;
//            $package->save();

            $package->company_sent = 1;
            $package->save();

            $package->track->bot_comment = "Bağlama YeniPoct-ə göndərildi - ".$assignAdmin;
            $package->track->save();

//            dispatch(new SendPackageToYeniPoctJob($package))->onQueue('default');
        }

        $container->update([
            'status' => YenipoctOrder::STATUSES['SENT'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);



        return back()->withSuccess('Göndərilməyib statusunda olan bağlamalar YeniPoct\'a göndərilir!');
    }

    public function handle()
    {





        $package = new Package();

        $package->warehouse_id = 11;
        $package->tracking_code = '9622001900009795283000432826834502';
        $package->ukr_express_id = 14056605;
        $package->user_id = 35035; // Alim Salehzade
        $package->show_label = 1;
        $package->u_tracing_code = '9622001900009795283000432826834502';
        $package->status = 0; // In Warehouse
        $package->weight = 12.00;
        $package->weight_goods = 12.00;
        $package->weight_type = 0;
        $package->width = null;
        $package->height = null;
        $package->length = null;
        $package->length_type = 0;
        $package->id = 45643;



        $package->bot_comment = 'tracking-customer-assigned';
        $package->ukr_express_status = 3;
        $package->ukr_express_error_at = null;

        $package->custom_id = 'ASE7873130831231';
        $package->website_name = '-';

        $package->additional_delivery_final_price = 0;
        $package->battery_price = 0;
        $package->insurance_price = 0;

// timestamps auto-dursa bunlara ehtiyac yoxdur
        $package->created_at = '2026-01-23 21:28:04';
        $package->updated_at = '2026-01-23 21:28:04';

        $user = User::find($package->user_id);
        $warehouse = Warehouse::find($package->warehouse_id);





        $user = User::find($package->user_id);
        $azerpoct = 0;
        $city_id = 0;
        if ($user) {
            $azerpoct = $user->azerpoct_send;
            $city_id = $user->city_id;
        }
        $package->custom_id = $package->custom_id ?: $package::generateCustomId();

        $webSiteName = getOnlyDomainWithExt($package->website_name);
        $package->website_name = $webSiteName ?: $package->website_name;

        $type_id = $package->type_id;
        $customs_type_id = null;
        if (isset($package->customs_type_id))
            $customs_type_id = $package->customs_type_id;
        $number_items = $package->number_items;


        if (!empty($number_items) && !empty($customs_type_id)) {
            $customsType = CustomsType::find($customs_type_id);
            if ($customsType)
                $package->detailed_type = $number_items . ' x ' . $customsType->name_en_with_parent;
        } else if (!empty($number_items) && !empty($type_id)) {
            $type = PackageType::find($type_id);
            if ($type)
                $package->detailed_type = $number_items . ' x ' . $type->translateOrDefault('en')->name;
        }



        //if ($package->country_id and ! $package->warehouse_id) {
        if ($package->country_id || $package->warehouse_id) {
            $warehouse = null;
            if ($package->country_id)
                $warehouse = Warehouse::whereCountryId($package->country_id)->latest()->first();
            else if ($package->warehouse_id)
                $warehouse = Warehouse::where('id', $package->warehouse_id)->latest()->first();


            if ($warehouse) {
                $package->warehouse_id = $warehouse->id;
                $weight = $package->weight_goods;

                $curShippingAmount = Package::s_getShippingAmountUSD($package);
                if (empty($weight))
                    $weight = $package->weight;
                $weight_type = $package->weight_type;
                if (!$weight_type) $weight_type = 0;
                $length_type = $package->length_type;
                if (!$length_type) $length_type = 0;



                if ($weight &&  request()->get('name') != 'delivery_price') {

                    $additionalDeliveryPrice = 0;

                    $additional_delivery_final_price = 0;
                    if (isset($package->additional_delivery_price) && $package->additional_delivery_price && $package->additional_delivery_price > 0 && $warehouse->use_additional_delivery_price)
                        $additional_delivery_final_price = $package->additional_delivery_price * 1.2;
                    $package->additional_delivery_final_price = $additional_delivery_final_price;
                    $additionalDeliveryPrice += $additional_delivery_final_price;

                    $battery_price = 0;
                    if (isset($package->has_battery) && $package->has_battery && $warehouse->battery_price && $warehouse->battery_price > 0)
                        $battery_price = $warehouse->battery_price;
                    $package->battery_price = $battery_price;
                    $additionalDeliveryPrice += $battery_price;

                    $insurance_price = 0;
                    if ($curShippingAmount && isset($package->has_insurance) && $package->has_insurance)
                        $insurance_price = $curShippingAmount * 0.01;
                    $package->insurance_price = $insurance_price;
                    $additionalDeliveryPrice += $insurance_price;


                    $discount_percent = $package->getUserDiscountPercent($user,$warehouse->country_id);
                    $deliveryPrice = $warehouse->calculateDeliveryPrice2($weight, $weight_type, $package->width, $package->height, $package->length, $length_type, false, 0, $azerpoct, $city_id, $additionalDeliveryPrice, $package->custom_id);
                    $package->delivery_price = $deliveryPrice;




                }
            }
        }

        dd($package->delivery_price);

        if ($package->warehouse_id && in_array($package->status, [0, 1, 2])) {
            $cdate = Carbon::now();
            $discounts = Discount::where('warehouse_id', $package->warehouse_id)->where('is_active', 1)->where('start_at', '<=', $cdate)->where('stop_at', '>=', $cdate)->get();
            $discountPercent = 0;
            foreach ($discounts as $discount) {
                $discountPercent += $discount->percent;
            }
            if ($discountPercent > 0) {
                if ($discountPercent > 100)
                    $discountPercent = 100;
                $package->discount_percent = $discountPercent;
                $package->discount_at = $cdate;
            }
        }

        if (empty($package->shipping_amount_goods) && !empty($package->shipping_amount)) $package->shipping_amount_goods = $package->shipping_amount;
        if (empty($package->number_items_goods) && !empty($package->number_items)) $package->number_items_goods = $package->number_items;
        if (empty($package->weight_goods) && !empty($package->weight)) $package->weight_goods = $package->weight;
        if ($package->do_use_goods != null && !$package->do_use_goods)
            $package->use_goods = 0;

        dd($package->delivery_price);



        $package = Package::find(391567);
        $ukraineExpress = new UkraineExpress2();
        $ukraineExpress->package_add($package);

        dd("salam");



        Notification::sendPackageTest(311312, 'Precint_notpaid');

        dd("salam");

        $unkrainerExpress2= new UkraineExpress2();

        dd($unkrainerExpress2->packages_update_from_ukr('TBADD0039624658'));

        $count = 100;//$type == 'SMS' ? 40 : 24;

//        dd("yes");

        $queues = NotificationQueue::whereIn('id',[
            2293639
        ])->whereNotNull('error_message')->get();
        $type = 'SMS';


//        foreach ($queues as $queue) {
//            echo $queue->id . "\t".$queue->to."\n";
//        }
//
//        exit;

        $num = 0;
        foreach ($queues as $queue) {
            if($queue->id == 2271352 or $queue->id==2271358) {
                continue;
            }
            $num++;
            $this->line($num . '  ' . $queue->to . " => " . $queue->subject);
            try {
                Notification::sendBothForQueueOnlySmS($queue);
                $queue->sent = 1;
                $queue->error_message = '';
                $queue->save();
                $this->line('success' . $queue->to);
            } catch (Exception $exception) {
                $message = null;
                $message .= "🆘 <b>Error by sending notification</b> " . $queue->to;
                $message .= chr(10) . $exception->getMessage();
                $queue->error_message = $exception->getMessage();

                if ($type == "WHATSAPP") {
                    $content = json_decode($queue->content);
                    $content = $content->sms;
                    $queue->type = 'SMS';
                    $queue->content = $content;
                    $queue->sent = 0;
                    // this message should not send with SMS notification
                    if (strpos($content, 'çatdırılma ünvanınızın konumunu') !== false) {
                        $queue->sent = 1;
                    }

                }else{
                    $queue->sent = 2;
                }
                $queue->save();
//                    sendTGMessage($message);
            }

            if (($num % $count) == 0)
                sleep(5);
        }


        exit;


        $body = [
            "trackingNumber"=> '501333116835000',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/carriersposts/0/100',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'lang: az',
                'ApiKey: 8CD0F430D478F8E1DFC8E1311B20031E3A669607',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        dd($response);


        $items = Track::find(670092);

        $this->goods_idList = [];
        $this->name_of_goodsList = [];
        $symbol = [',', ';', '.', ':', '!', '?', '"', "'", '(', ')', '[', ']', '{', '}', '<', '>', '/', '\\', '|', '@', '#', '$', '%', '^', '&', '*', '+', '=', '~', '`'];

        $this->goods_idList[] = 0;
        $cleanedName = str_replace($symbol, '', $items->detailed_type);
        $cleanedName = substr($cleanedName, 0, 490);
        $name_of_goodsList[] = $cleanedName;

        dd($name_of_goodsList, $items->detailed_type);

        $kapitalBankTxpgService = new KapitalBankTxpgService();
        $transaction = Transaction::find(455327);

        $orderId = $transaction->source_id;
        $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);
        dd($orderStatus);
        exit;
        $package = Package::find(384095);

        $ukraineExpress = new UkrExpressModel();

        $test = $ukraineExpress->change_customer($package);

        dd($test);

        $body = [
            "trackingNumber"=> 'TEST202512090002AZ',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ecarrier-fbusiness.customs.gov.az:7545/api/v2/carriers/carriersposts/0/100',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'lang: az',
                'ApiKey: 8CD0F430D478F8E1DFC8E1311B20031E3A669607',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        dd($response);
        curl_close($curl);



        $testMode = false;
        $pCustomId = $this->option('cwb');
        if ($pCustomId)
            $testMode = true;

        $timeToRun = 5 * 60 - 10;
        $sendTelegram = true;
        $begin_tm = time();
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel(true);
        $items = DB::select("select * from customs_countries");
        $cm_countries = [];
        foreach ($items as $item) {
            $cm_countries[strtolower($item->CODE_C)] = $item->CODE_N;
        }

        /*$items = DB::select("select * from customs_currencies");
        foreach($items as $item)
        {
            $cm_currencies[strtolower($item->CODE_C)]=$item->CODE_N;
        }*/
        $this->info($ldate . "  ===== Started to add new carriers =====");
        if ($testMode)
            $this->info($ldate . "  ===== TEST MODE =====");

        $query = 'SELECT';
        $query .= ' t.id';
        $query .= ' ,pc.id as pc_id,pc.code as pc_code, t.partner_id, t.created_at';
        $query .= ' FROM tracks t';
        $query .= ' left outer join package_carriers pc on pc.track_id=t.id';
        $query .= ' WHERE (t.deleted_at is null)';
        if (empty($pCustomId)) {
//            $query .= " and (t.status in (1,2,5,6)) and (t.partner_id in (1))";
//            $query .= " and ((pc.id is null) or ((pc.status is null or pc.status=0) and (pc.code<>200) and (TIME_TO_SEC(TIMEDIFF('" . $ldate . "',pc.created_at))>3600)))";



            $query .= " AND t.status IN (1,2,5,6)
            AND t.partner_id IN (1)";

            $query .= " AND (
                pc.id IS NULL
                OR (
                    (pc.status IS NULL OR pc.status = 0)
                    AND pc.code <> 200
                    AND TIME_TO_SEC(TIMEDIFF('" . $ldate . "', pc.created_at)) > 3600
                )
            )";

                        $query .= " AND (
                t.partner_id <> 1
                OR (t.partner_id = 1 AND t.created_at >= '2025-12-24')
            )";



        } else
            $query .= " and t.tracking_code in ('$pCustomId')";
        //$query.=" and (t.created_at >= '2024-07-01 00:00:00')))";
        $query .= ' and (t.weight is not null and t.weight>0)';
//        $query .= " and (t.tracking_code = '450420047467000')";
        $queryOne = $query;
        $query .= ' ORDER BY t.created_at';
        $query .= '';
        $pn = 0;
        $items = DB::select($query);

        foreach ($items as $item) {

            $track = Track::find($item->id);
            //print_r($track);
            //continue;
            $now_tm = time();
            if (($now_tm - $begin_tm) >= $timeToRun) {
                $this->info($ldate . "  ===== Timeout =====");
                return;
            }

            if ($pn > 0)
                sleep(3);

            $pn++;
            $ldate = date('Y-m-d H:i:s');

            if ($track->customer && $track->customer->fin && $track->customer->fin != '-') {
                $cm->fin = $track->customer->fin;
            } elseif ($track->fin && $track->fin != '-') {
                $cm->fin = $track->fin;
            } else {
                $cm->fin = null;
            }

            $cm->isCommercial = 0;
            $cm->trackingNumber = $track->tracking_code;

            //Delete from customs system
            /*if (!empty($item->pc_id) && $item->pc_code == 400) {
                $res = $cm->delete_carriers();
                if (!isset($res->code)) {
                    $this->info($ldate . " $pn Error Track: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                    $this->info("    Cannot remove track ");
                    //continue;
                } else
                    if ($res->code != 200)// && ($res->code != 400))
                    {
                        $cm->parse_error($res);
                        $this->info($ldate . " $pn Error Track: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                        $this->info("Cannot remove track from customs system(" . $res->code . "): ");
                        //continue;
                    } else {
                        DB::delete("delete from package_carriers where id=?", [$item->pc_id]);
                        $this->info($ldate . " $pn Deleted Track: " .  $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                        $item->pc_id = NULL;
                    }
                sleep(3);
                $ldate = date('Y-m-d H:i:s');
	    }*/
            //---------

            $pc_id = $item->pc_id;
            if (!empty($pc_id)) {
                $ones = DB::select($queryOne . " and pc.id=" . $pc_id);
                if (count($ones) <= 0) {
                    $this->info($ldate . "    Error: DB changed ");
                    $item = null;
                    continue;
                }
                $item = $ones[0];
                $track = Track::find($track->id);
            }
            $cm->phone = $track->phone;
            if (!$cm->phone)
                $cm->phone = $track->customer->phone;
            $fullName = $track->fullname;
            if (!$fullName)
                $fullName = $track->customer->fullname;
            $address = $track->address;

            $countryCode = '';
            if ($track->partner_id == 1)
                $countryCode = 'us';
            if ($track->partner_id == 2)
                $countryCode = 'ru';
            if ($track->partner_id == 3)
                $countryCode = 'ru';
            if ($track->partner_id == 3 && $track->from_country == "TR")
                $countryCode = 'tr';
            if ($track->partner_id == 8)
                $countryCode = 'cn';
            if ($track->partner_id == 9)
                $countryCode = 'cn';

            //check for fin
            if (empty($cm->fin) || strlen($cm->fin) > 9 || strlen($cm->fin) < 5 || empty(trim($address)) || !array_key_exists($countryCode, $cm_countries) || !$track->weight || !$track->number_items/*|| ($package->u_is_commercial && empty($package->u_voen))*/) {
                $errorMessage = '';
                $validationError = '';
                $this->info($ldate . " $pn Error Track: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                $message = "🛑 Eror checking track for customs system\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber . "</a>\n";
                if (empty($cm->fin)) {
                    $this->info("    Empty fin ");
                    $message .= "Error: Empty fin code\n";
                    $validationError = "Empty FIN Code";
                } else if (strlen($cm->fin) > 9 || strlen($cm->fin) < 5) {
                    $this->info("    Wrong fin length " . $cm->fin);
                    $message .= "Error: Wrong fin code " . $cm->fin . "\n";
                    $validationError = "Wrong FIN Code " . $cm->fin;
                } else if (!$cm->weight) {
                    $this->info("    Empty weight ");
                    $message .= "Error: Empty Weight\n";
                    $validationError = "Empty Weight";
                } else if (!$cm->number_items) {
                    $this->info("    Empty number_items ");
                    $message .= "Error: Empty Number Items\n";
                    $validationError = "Empty Number Items";
                } else if (empty(trim($address))) {
                    $this->info("    Empty address ");
                    $message .= "Error: Empty address\n";
                    $validationError = "Empty ADDRESS";
                } else if (!array_key_exists($countryCode, $cm_countries)) {
                    $this->info("    Wrong country code: " . $countryCode);
                    $message .= "Error: Wrong country code $countryCode\n";
                    $validationError = "Wrong COUNTRY";
                }
                /*if ($package->u_is_commercial && empty($package->u_voen)) {
                    $this->info("    Commercial user has no voen");
                    $message .= "Commercial user has no voen\n";
                    $validationError = "Empty VOEN";
		}*/
                if ($sendTelegram && !$testMode) sendTGMessage($message);
                if (!$testMode) {
                    if (!empty($pc_id)) {
                        DB::update("update package_carriers set track_id=?,fin=?,trackingNumber=?,code=?,errorMessage=?,validationError=?,created_at=? where id=?"
                            , [$track->id, $cm->fin, $cm->trackingNumber, 998, $errorMessage, $validationError, $ldate, $pc_id]);
                    } else {
                        DB::insert("insert into package_carriers (track_id,fin,trackingNumber,code,errorMessage,validationError,created_at) values (?,?,?,?,?,?,?)"
                            , [$track->id, $cm->fin, $cm->trackingNumber, 998, $errorMessage, $validationError, $ldate]);
                    }
                }
                $track->status = 6;
                $track->bot_comment .= "Error:CarriersTrackAdd " . $validationError . "\n";

                $track->save();
                (new PackageService())->updateStatus($track, 6);
                continue;
            }
            //----
            $shippingAmount = $track->shipping_amount;
            if (!$shippingAmount)
                $shippingAmount = 0;
            $deliveryAmount = $track->delivery_price;
            if (!$deliveryAmount)
                $deliveryAmount = 0.01;


            /*$warehouse = Warehouse::find($package->w_id);
                if ($warehouse && $package->weight) {
                    $deliveryAmount = $warehouse->calculateDeliveryPrice($package->weight, $package->weight_type,
                                                                $package->width, $package->height, $package->length, $package->length_type);
            }*/
            $webSiteName = getOnlyDomainWithExt($track->website);
            if (empty($webSiteName) || $webSiteName == '-') {
                $webSiteName = $track->partner->website;
            }
            if (empty($webSiteName) || $webSiteName == '-') {
                if ($track->partner_id == 1)
                    $webSiteName = 'iherb.com';
                if ($track->partner_id == 2)
                    $webSiteName = 'wildberries.ru';
                if ($track->partner_id == 3)
                    $webSiteName = 'ozon.ru';
            }

            $addressStr = $track->partner->address;
            //$this->info("------------------");
            //$this->info("id:".$package->id);
            //$cm->get_carriers_goods($_package->customs_type_id, $TypeId, $TypeStr, $package->id);
            //$cm->goods_idList = [0];
            //$cm->name_of_goodsList = ['-'];

            if ($track->partner_id == 3) {
                $cm->get_goods_noid_ozon($track);
            } elseif ($track->partner_id == 9) {
                $cm->get_goods_noid_taobao($track);
            } else {
                $cm->get_goods_noid($track->goods);
            }
            $whtsp = array("\r\n", "\n", "\r");
            $cm->direction = 1;
            $cm->trackinG_NO = $cm->trackingNumber;
            if ($track->delivery_price_cur)
                $cm->transP_COSTS = convertToUSD($deliveryAmount, $track->delivery_price_cur);
            else
                $cm->transP_COSTS = $deliveryAmount;

            if (!$cm->transP_COSTS)
                $cm->transP_COSTS = rand(10, 100) / 100;
            $cm->weighT_GOODS = $track->weight;
            if (!$cm->weighT_GOODS)
                $cm->weighT_GOODS = 0.01;
            $cm->quantitY_OF_GOODS = $track->number_items;
            if (!$cm->quantitY_OF_GOODS)
                $cm->quantitY_OF_GOODS = 0;
            $cm->invoyS_PRICE = $shippingAmount;


            if ($track->currency && in_array($track->currency, array_values(config('ase.attributes.customsCurrencies')))) {
                foreach (config('ase.attributes.customsCurrencies') as $c_key => $c_value) {
                    if ($c_value == $track->currency) {
                        if ($track->partner_id == 3) {
                            $cm->currencY_TYPE = 840;
                            break;
                        } else {
                            $cm->currencY_TYPE = $c_key;
                            break;
                        }
                    }
                }
            } else {
                $cm->currencY_TYPE = 840;
            }
            //Ozon new currency type and price
            if ($track->partner_id == 3) {
                $currency = $track->delivery_price_cur ?? 'KZT';
                $cm->invoyS_PRICE = convertToUSD($shippingAmount, $currency);
                $cm->currencY_TYPE = 840;
            }

//            if ($track->partner_id == 3) {
//                $cm->invoyS_PRICE = $track->delivery_amount_usd;
//                $cm->currencY_TYPE = 840;
//            }

            $cm->document_type = "PinCode";
            if (strlen($cm->fin) == 9 /*&& strtoupper(substr($cm->fin, 0, 1)) == 'P'*/)
                $cm->document_type = "PassportNumber";
            $cm->idxaL_NAME = str_replace('"', '\"', $fullName);
            $cm->idxaL_ADRESS = $address;
            $cm->idxaL_ADRESS = str_replace("\\", "\\\\", $cm->idxaL_ADRESS);
            $cm->idxaL_ADRESS = str_replace('"', '\"', $cm->idxaL_ADRESS);
            $cm->idxaL_ADRESS = str_replace($whtsp, ' ', $cm->idxaL_ADRESS);
            $cm->phone = str_replace("\\", "\\\\", $cm->phone);
            $cm->ixraC_NAME = str_replace('"', '\"', $webSiteName);
            $cm->ixraC_ADRESS = str_replace('"', '\"', $addressStr);
            $cm->goodS_TRAFFIC_FR = $cm_countries[$countryCode];
            $cm->goodS_TRAFFIC_TO = "031";

            /*if ($cm->isCommercial) {
                $cm->voen = $package->u_voen;
                if (empty($package->pl_custom_id))
                    $cm->airwaybill = $package->custom_id;
                else
                    $cm->airwaybill = $package->pl_custom_id;
                if (empty($package->b_custom_id))
                    $cm->depesH_NUMBER = $package->custom_id;
                else
                    $cm->depesH_NUMBER = $package->b_custom_id;
                if (!empty($package->u_company))
                    $cm->idxaL_NAME = str_replace('"', '\"', $package->u_company);
	    }*/

            //$this->info($cm->get_carriers_json_str());
            if ($testMode) {
                $this->info($cm->get_carriersposts_url());
                $this->info($cm->get_carriers_json_str());
                //continue;
            }

            $pc_id = $item->pc_id;
            $res = $cm->add_carriers();
            $ldate = date('Y-m-d H:i:s');

            if (!isset($res->code)) {
                $this->info($ldate . " $pn Error Track: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                $this->info("    Empty response (retry)");
                sleep(1);
                $res = $cm->add_carriers();
                $ldate = date('Y-m-d H:i:s');
            }

            $pc_id = $item->pc_id;

            if (!empty($pc_id)) {
                $ones = DB::select("select id from package_carriers where id=" . $pc_id);
                if (count($ones) <= 0) {
                    $pc_id = null;
                }
            }


            if (!isset($res->code)) {
                $this->info($ldate . " $pn Error Track: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                $this->info("    Empty response ");
                $message = "🛑 Eror adding track to customs system\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber . "</a>\n";
                $message .= "Error: Empty response\n";
                $message .= "phone: " . $cm->phone . "\n";
                $message .= "address: " . $cm->idxaL_ADRESS . "\n";
                //$message.= $cm->get_carriers_html_str();
                $this->info("  ----*******----- ");
                $this->info($cm->get_carriers_json_str());
                $this->info("  ----*******----- ");
                print_r($res);
                $this->info("  ----*******----- ");
                //$message.=$cm->get_carriers_html_str();
                if ($sendTelegram && !$testMode) sendTGMessage($message);
                $cm->updateTrackDB2($track->id, $cm->fin, $cm->trackingNumber, $ldate, 999);
                $track->status = 6;
                $track->save();
                (new PackageService())->updateStatus($track, 6);
                continue;
            }
            //print_r($res);
            if (($res->code == 400) && isset($res->exception) && is_object($res->exception) && isset($res->exception->status) && $res->exception->status == 'error')
                $res->code = 888;
            $cm->updateTrackDB2($track->id, $cm->fin, $cm->trackingNumber, $ldate, $res->code, NULL, $cm->idxaL_NAME, $cm->ixraC_NAME);
            if ($res->code == 200) {
                $this->info($ldate . " $pn  Ok Track: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber . " added");
                /*
                $message="✅ Track added to customs system (".$res->code.")\n";
                $message.="<b>".$fullName."</b>";
                    $message.="   <a href='https://admin."  . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber ."</a>\n";
                if($sendTelegram) sendTGMessage($message); */
                $track->status = 5;
                $track->save();
                Notification::sendTrack($track->id, 5);
                (new PackageService())->updateStatus($track, 5);

            } else {
                $errorMessage = '';
                $validationError = '';
                if (isset($res->exception) && is_object($res->exception)) {
                    $exception = $res->exception;
                    $errorMessage = $exception->errorMessage;
                    //print_r($exception);
                    $errs = [];
                    if (is_array($exception->validationError))
                        $errs = $exception->validationError;
                    if (is_object($exception->validationError))
                        $errs = get_object_vars($exception->validationError);
                    foreach ($errs as $x => $x_value) {
                        if (!empty($validationError))
                            $validationError .= " , ";
                        $validationError .= $x . "=>" . $x_value;
                    }
                    //$validationError=json_encode($exception->validationError);
                }
                $this->info($ldate . " $pn Error (" . $res->code . ") Package: " . $track->id . " fin:" . $cm->fin . " trackNo:" . $cm->trackingNumber);
                $this->info("    errorMessage: " . $errorMessage);
                $this->info("    validationError: " . $validationError);
                $this->info("  ----*******----- ");
                print_r($res);
                $this->info("  ----*******----- ");
                //print_r($res);
                //$this->info("  --------- ");
                $this->info($cm->get_carriers_json_str());
                $this->info("  ----*******----- ");
                $message = "🛑 Eror adding track to customs system (" . $res->code . ")\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber . "</a>\n";
                if (!empty($errorMessage))
                    $message .= "errorMessage: " . $errorMessage . "\n";
                if (!empty($validationError))
                    $message .= "validationError: " . $validationError . "\n";
                //$message.=$cm->get_carriers_html_str();
                if ($sendTelegram && !$testMode) sendTGMessage($message);
                //$this->info("Telegram message: $message");
                //$this->info("Telegram result: $gt_res");
                $track->status = 6;
                $track->save();
                (new PackageService())->updateStatus($track, 6);
            }
        }
        //
    }
}
