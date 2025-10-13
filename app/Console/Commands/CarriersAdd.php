<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Package;
use DB;
use Illuminate\Console\Command;

class CarriersAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:add {--type=insert} {--cwb=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add carriers from packages';

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
        $pCustomId = '';
        if ($this->option('type') == 'test') {
            $testMode = true;
            $pCustomId = $this->option('cwb');
        }

        $timeToRun = 5 * 60 - 10;
        $sendTelegram = true;
        $begin_tm = time();
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
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
        $query = 'SELECT ';
        $query .= ' pl.id as pl_id,pl.custom_id as pl_custom_id,b.custom_id as b_custom_id';
        $query .= ' ,pc.id as pc_id,p.id,pc.code as pc_code';
        $query .= ' ,p.number_items_goods,p.weight_goods,p.weight_type,p.width,p.height,p.length,p.length_type';
        $query .= ' ,p.website_name,p.type_id,p.detailed_type,p.shipping_amount_goods,p.shipping_amount_cur,p.delivery_price,p.custom_id,p.country_id';
        $query .= ' ,c.code,wc.code as w_code,a.zip_code,coalesce(c_en.name,c_az.name,c_ru.name) as country_name';
        $query .= ' ,w.id as w_id,w.company_name as w_company_name,w.web_site as w_web_site,w.currency as w_currency,a.address_line_1,a.city,a.state';
        $query .= ' ,u.name,u.surname,u.phone,u.address,u.fin,u.passport,u.customer_id as u_customer_id,u.check_customs as u_check_customs,u.is_commercial as u_is_commercial,u.voen as u_voen,u.company as u_company';
        $query .= ' FROM packages p ';
        $query .= ' LEFT OUTER JOIN parcel_package pp on pp.package_id=p.id';
        $query .= ' LEFT OUTER JOIN parcels pl on pp.parcel_id=pl.id';
        $query .= ' LEFT OUTER JOIN bag_package bp on bp.package_id=p.id';
        $query .= ' LEFT OUTER JOIN bags b on bp.bag_id=b.id';
        $query .= ' LEFT OUTER JOIN package_types t on p.type_id=t.id';
        $query .= ' left outer join package_carriers pc on pc.package_id=p.id';

        $query .= ' LEFT OUTER JOIN users u on p.user_id=u.id';
        $query .= ' LEFT OUTER JOIN countries c on p.country_id=c.id';
        $query .= ' LEFT OUTER JOIN warehouses w on w.id=p.warehouse_id';
        $query .= ' LEFT OUTER JOIN countries wc on w.country_id=wc.id';

        $query .= " left outer join country_translations c_az on (c_az.locale='az' and c_az.country_id=wc.id)";
        $query .= " left outer join country_translations c_en on (c_en.locale='en' and c_en.country_id=wc.id)";
        $query .= " left outer join country_translations c_ru on (c_ru.locale='ru' and c_ru.country_id=wc.id)";

        $query .= ' LEFT OUTER JOIN addresses a on a.warehouse_id=w.id';
        //$query.=" WHERE ((p.status = 0) or ((p.status=1) and (p.updated_at >= '2020-12-01 00:00:00')))";
        if (empty($pCustomId))
            $query .= " WHERE ((p.status = 0 or p.status=47) or ((p.status=1) and (TIME_TO_SEC(TIMEDIFF('" . $ldate . "',p.updated_at))<=30*86400)))";
        else
            $query .= " WHERE p.custom_id in ('$pCustomId')";
        //$query.=" WHERE p.custom_id in ('ASE3860990266061')";
        $query .= " and ((u.check_customs=1) or (u.check_customs=0 and pc.id is null))";
        $query .= ' and a.id in (select max(id) from addresses group by warehouse_id)';
        $query .= " and ((w.customs_auto_delcaration=1) or (";
        //$query.=' and ((p.number_items_goods is not null and p.number_items_goods > 0) or (p.number_items_goods is not null and p.number_items_goods > 0)) ';
        $query .= ' ((p.number_items_goods is not null and p.number_items_goods > 0) or (p.number_items_goods is not null and p.number_items_goods > 0)) ';
        $query .= ' and ((p.shipping_amount is not null and p.shipping_amount > 0) or (p.shipping_amount_goods is not null and p.shipping_amount_goods > 0))';
        $query .= " ))";
        $query .= ' and ((p.weight is not null and p.weight > 0) or (p.weight_goods is not null and p.weight_goods > 0)) ';
        $query .= ' and p.deleted_at is null and u.deleted_at is null';
        if (!$testMode)
            $query .= " and ((pc.id is null) or ((pc.status<=0) and (pc.code<>200) and (TIME_TO_SEC(TIMEDIFF('" . $ldate . "',pc.created_at))>3600)))";
        //$query.=" and ((pc.id is null) or (pc.code<>200))";
        //$query.=" and ((pc.id is null) or ((pc.code<>200 and pc.code<>400) and (TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>3600)))";
        $queryOne = $query;
        //$query.=" and ((pc.id is null) or ((pc.code<>200 and pc.code<>400) and (TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>3*60)))";
        //$query.=' and p.id in(46738,7020)';
        $query .= ' ORDER BY p.created_at DESC';
        $query .= ' limit 100';
        //$this->info($query);
        //return;
        $pn = 0;
        $packages = DB::select($query);
        //dd($packages);
        foreach ($packages as $package) {
            //print_r($package);
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

            $cm->fin = $package->fin;
            $cm->isCommercial = $package->u_is_commercial;
            $cm->trackingNumber = $package->custom_id;

            //Delete from customs system
            /*            if (!empty($package->pc_id) && $package->pc_code == 400) {
                            $res = $cm->delete_carriers();
                            if (!isset($res->code)) {
                                $this->info($ldate . " $pn Error Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                                $this->info("    Cannot remove package ");
                                //continue;
                            } else
                                if ($res->code != 200)// && ($res->code != 400))
                                {
                                    $cm->parse_error($res);
                                    $this->info($ldate . " $pn Error Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                                    $this->info("Cannot remove package from customs system(" . $res->code . "): ");
                                    //continue;
                                } else {
                                    DB::delete("delete from package_carriers where id=?", [$package->pc_id]);
                                    $this->info($ldate . " $pn Deleted Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                                    $package->pc_id = NULL;
                                }
                            sleep(3);
                            $ldate = date('Y-m-d H:i:s');
                    }*/
            //---------
            $cm->pinNumber = $package->fin;
            $cpost = $cm->get_carrierposts2();
            if ($cpost->code == 200 && $cpost->inserT_DATE) {
                $this->info($ldate . " $pn Exists Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                $cm->updateDB2($package->id, $package->fin, $package->custom_id, $ldate, 200);
                continue;
            }

            $pc_id = $package->pc_id;
            if (!$package->u_check_customs) {
                if (empty($pc_id) && !$testMode)
                    DB::insert("insert into package_carriers (package_id,fin,trackingNumber,code,check_customs,created_at) values (?,?,?,0,0,?)"
                        , [$package->id, $package->fin, $package->custom_id, $ldate]);
                continue;
            }
            if (!empty($pc_id)) {
                $ones = DB::select($queryOne . " and pc.id=" . $pc_id);
                if (count($ones) <= 0) {
                    $this->info($ldate . "    Error: DB changed ");
                    $package = null;
                    continue;
                }
                $package = $ones[0];
            }
            $fullName = $package->name;
            $surname = $package->surname;
            if (!empty($surname))
                $fullName .= ' ' . $surname;
            $countryCode = strtolower($package->w_code);
            if ($countryCode == 'uk') $countryCode = 'gb';
            if ($countryCode == 'uae') $countryCode = 'ae';
            //check for fin
            if (empty($package->fin) || empty(trim($package->address)) || !array_key_exists($countryCode, $cm_countries) || ($package->u_is_commercial && empty($package->u_voen))) {
                $errorMessage = '';
                $validationError = '';
                $this->info($ldate . " $pn Error Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                $message = "🛑 Eror checking package for customs system\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                if (empty($package->fin)) {
                    $this->info("    Empty fin ");
                    $message .= "Error: Empty fin code\n";
                    $validationError = "Empty FIN Code";
                }
                if (empty(trim($package->address))) {
                    $this->info("    Empty address ");
                    $message .= "Error: Empty address\n";
                    $validationError = "Empty ADDRESS";
                }
                if (!array_key_exists($countryCode, $cm_countries)) {
                    $this->info("    Wrong country code: " . $countryCode);
                    $message .= "Error: Wrong country code $countryCode\n";
                    $validationError = "Wrong COUNTRY";
                }
                if ($package->u_is_commercial && empty($package->u_voen)) {
                    $this->info("    Commercial user has no voen");
                    $message .= "Commercial user has no voen\n";
                    $validationError = "Empty VOEN";
                }
                if ($sendTelegram && !$testMode) sendTGMessage($message);
                if (!$testMode) {
                    if (!empty($pc_id)) {
                        DB::update("update package_carriers set package_id=?,fin=?,trackingNumber=?,code=?,errorMessage=?,validationError=?,created_at=? where id=?"
                            , [$package->id, $package->fin, $package->custom_id, 998, $errorMessage, $validationError, $ldate, $pc_id]);
                    } else {
                        DB::insert("insert into package_carriers (package_id,fin,trackingNumber,code,errorMessage,validationError,created_at) values (?,?,?,?,?,?,?)"
                            , [$package->id, $package->fin, $package->custom_id, 998, $errorMessage, $validationError, $ldate]);
                    }
                }
                continue;
            }
            //----
            $_package = Package::find($package->id);
            $shippingAmount = $_package->getShippingAmountUSD();
            if (!$shippingAmount)
                $shippingAmount = 0;
            $deliveryAmount = $_package->delivery_usd_price_discount;


            /*$warehouse = Warehouse::find($package->w_id);
                if ($warehouse && $package->weight) {
                    $deliveryAmount = $warehouse->calculateDeliveryPrice($package->weight, $package->weight_type,
                                                                $package->width, $package->height, $package->length, $package->length_type);
            }*/
            $webSiteName = getOnlyDomainWithExt($package->website_name);
            $webSiteName = $webSiteName ?: $package->website_name;
            $TypeId = $package->type_id;
            $TypeStr = $package->detailed_type;

            $addressStr = '';
            $str = $package->w_company_name;
            if (!empty(trim($str))) {
                if (!empty($addressStr))
                    $addressStr .= ", ";
                $addressStr .= $str;
            }
            $str = $package->address_line_1;
            if (!empty(trim($str))) {
                if (!empty($addressStr))
                    $addressStr .= ", ";
                $addressStr .= $str;
            }
            $str = $package->city;
            if (!empty(trim($str))) {
                if (!empty($addressStr))
                    $addressStr .= ", ";
                $addressStr .= $str;
            }
            $str = $package->state . " " . $package->zip_code;
            if (!empty(trim($str))) {
                if (!empty($addressStr))
                    $addressStr .= ", ";
                $addressStr .= $str;
            }
            $str = $package->country_name;
            if (!empty(trim($str))) {
                if (!empty($addressStr))
                    $addressStr .= ", ";
                $addressStr .= $str;
            }
            //$this->info("------------------");
            //$this->info("id:".$package->id);
            $cm->get_carriers_goods($_package->customs_type_id, $TypeId, $TypeStr, $package->id);

            $whtsp = array("\r\n", "\n", "\r");
            $cm->direction = 1;
            $cm->trackinG_NO = $package->custom_id;
            $cm->transP_COSTS = $deliveryAmount;
            $cm->weighT_GOODS = $_package->getWeight();//weight_goods;
            if (!$cm->weighT_GOODS)
                $cm->weighT_GOODS = 0;
            $cm->quantitY_OF_GOODS = $_package->getNumberItems();//number_items_goods;
            if (!$cm->quantitY_OF_GOODS)
                $cm->quantitY_OF_GOODS = 0;
            $cm->invoyS_PRICE = $shippingAmount;
            $cm->currencY_TYPE = "840";
            $cm->fin = $package->fin;
            $cm->document_type = "PinCode";
            if (strtoupper($package->fin) == strtoupper($package->passport))
                $cm->document_type = "PassportNumber";
            $cm->idxaL_NAME = str_replace('"', '\"', $fullName);
            $cm->idxaL_ADRESS = $package->address;
            $cm->idxaL_ADRESS = str_replace("\\", "\\\\", $cm->idxaL_ADRESS);
            $cm->idxaL_ADRESS = str_replace('"', '\"', $cm->idxaL_ADRESS);
            $cm->idxaL_ADRESS = str_replace($whtsp, ' ', $cm->idxaL_ADRESS);
            $cm->phone = $package->phone;
            $cm->phone = str_replace("\\", "\\\\", $cm->phone);
            $cm->ixraC_NAME = str_replace('"', '\"', $webSiteName);
            $cm->ixraC_ADRESS = str_replace('"', '\"', $addressStr);
            $cm->goodS_TRAFFIC_FR = $cm_countries[$countryCode];
            $cm->goodS_TRAFFIC_TO = "031";

            $cm->isCommercial = $package->u_is_commercial;
            if ($package->u_is_commercial) {
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
            }

            //$this->info($cm->get_carriers_json_str());
            if ($testMode) {
                $this->info($cm->get_carriersposts_url());
                $this->info($cm->get_carriers_json_str());
                continue;
            }

            $pc_id = $package->pc_id;
            $res = $cm->add_carriers();
            $ldate = date('Y-m-d H:i:s');

            if (!isset($res->code)) {
                $this->info($ldate . " $pn Error Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                $this->info("    Empty response (retry)");
                sleep(1);
                $res = $cm->add_carriers();
                $ldate = date('Y-m-d H:i:s');
            }

            $pc_id = $package->pc_id;

            if (!empty($pc_id)) {
                $ones = DB::select("select id from package_carriers where id=" . $pc_id);
                if (count($ones) <= 0) {
                    $pc_id = null;
                }
            }


            if (!isset($res->code)) {
                $this->info($ldate . " $pn Error Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                $this->info("    Empty response ");
                $message = "🛑 Eror adding package to customs system\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
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
                if ($sendTelegram) sendTGMessage($message);
                $cm->updateDB2($package->id, $package->fin, $package->custom_id, $ldate, 999);
                continue;
            }
            //print_r($res);
            if (($res->code == 400) && isset($res->exception) && is_object($res->exception) && isset($res->exception->status) && $res->exception->status == 'error')
                $res->code = 888;
            $cm->updateDB2($package->id, $package->fin, $package->custom_id, $ldate, $res->code, $package->country_id, $cm->idxaL_NAME, $cm->ixraC_NAME);
            if ($res->code == 200) {
                $this->info($ldate . " $pn  Ok Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id . " added");
                /*
                $message="✅ Package added to customs system (".$res->code.")\n";
                $message.="<b>".$fullName."</b>";
                    $message.="  (<a href='https://admin."  . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id ."</a>)";
                    $message.="   <a href='https://admin."  . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id ."</a>\n";
                if($sendTelegram) sendTGMessage($message); */

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
                $this->info($ldate . " $pn Error (" . $res->code . ") Package: " . $package->id . " fin:" . $package->fin . " trackNo:" . $package->custom_id);
                $this->info("    errorMessage: " . $errorMessage);
                $this->info("    validationError: " . $validationError);
                $this->info("  ----*******----- ");
                print_r($res);
                $this->info("  ----*******----- ");
                //print_r($res);
                //$this->info("  --------- ");
                $this->info($cm->get_carriers_json_str());
                $this->info("  ----*******----- ");
                $message = "🛑 Eror adding package to customs system (" . $res->code . ")\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                if (!empty($errorMessage))
                    $message .= "errorMessage: " . $errorMessage . "\n";
                if (!empty($validationError))
                    $message .= "validationError: " . $validationError . "\n";
                //$message.=$cm->get_carriers_html_str();
                if ($sendTelegram && $res->code != 400) sendTGMessage($message);
                //$this->info("Telegram message: $message");
                //$this->info("Telegram result: $gt_res");
            }
        }
        //
    }
}
