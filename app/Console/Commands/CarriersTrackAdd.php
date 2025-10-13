<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Track;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Console\Command;
use App\Services\Integration\UnitradeService;
use App\Models\Extra\Notification;

class CarriersTrackAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers_tracks:add {--type=insert} {--cwb=}';

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
    public function handle()
    {
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
        $query .= ' ,pc.id as pc_id,pc.code as pc_code';
        $query .= ' FROM tracks t';
        $query .= ' left outer join package_carriers pc on pc.track_id=t.id';
        $query .= ' WHERE (t.deleted_at is null)';
        if (empty($pCustomId)) {
            $query .= " and (t.status in (1,2,6)) and (t.partner_id in (3,8,9))";
            $query .= " and ((pc.id is null) or ((pc.status is null or pc.status=0) and (pc.code<>200) and (TIME_TO_SEC(TIMEDIFF('" . $ldate . "',pc.created_at))>3600)))";
        } else
            $query .= " and t.tracking_code in ('$pCustomId')";
        //$query.=" and (t.created_at >= '2024-07-01 00:00:00')))";
        $query .= ' and (t.weight is not null and t.weight>0)';
//        $query .= " and (t.tracking_code = '450420047467000')";
        $queryOne = $query;
        $query .= ' ORDER BY t.created_at';
        $query .= ' limit 100';
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
            }elseif ($track->partner_id == 9){
                $cm->get_goods_noid_taobao($track->goods);
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
                        }else{
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
