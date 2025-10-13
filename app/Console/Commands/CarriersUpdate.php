<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Package;
use DB;
use Illuminate\Console\Command;

class CarriersUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:update  {package} {package_id} {checkonly=1} {htmlformat=0} {deleteonly=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update carrier info of package';

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
        $checkOnly = $this->argument("checkonly");
        $deleteOnly = $this->argument("deleteonly");
        $packageOnly = $this->argument("package");
        $htmlFormat = $this->argument("htmlformat");
        $id = $this->argument('package_id');

        $sendTelegram = true;
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
        $cm->retryCount = 7;
        $cm->retrySleep = 0;
        $items = DB::select("select * from customs_countries");
        $cm_countries = [];
        foreach ($items as $item) {
            $cm_countries[strtolower($item->CODE_C)] = $item->CODE_N;
        }

        $items = DB::select("select * from customs_currencies");
        foreach ($items as $item) {
            $cm_currencies[strtolower($item->CODE_C)] = $item->CODE_N;
        }
        $query = 'SELECT ';
        $query .= '  pc.id as pc_id,p.id,p.status';
        $query .= ' ,p.id,p.custom_id as custom_id';
        $query .= ' ,pc.code as pc_code,pc.fin as pc_fin,pc.inserT_DATE,pc.created_at as pc_created_at,pc.ecoM_REGNUMBER,pc.depesH_NUMBER,pc.check_customs as pc_check_customs';
        $query .= ' ,p.number_items_goods,p.weight_goods,p.weight_type,p.width,p.height,p.length,p.length_type';
        $query .= ' ,p.website_name,p.type_id,p.detailed_type,p.shipping_amount_goods,p.shipping_amount_cur,p.delivery_price,p.custom_id,p.country_id';
        $query .= ' ,c.code,wc.code as w_code,a.zip_code,coalesce(c_en.name,c_az.name,c_ru.name) as country_name';
        $query .= ' ,w.id as w_id,w.company_name as w_company_name,w.web_site as w_web_site,w.currency as w_currency,w.customs_auto_delcaration as w_customs_auto_delcaration';
        $query .= ' ,a.address_line_1,a.city,a.state';
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
        if ($packageOnly == 1)
            $query .= ' WHERE p.id=' . $id;
        else if ($packageOnly == 0)
            $query .= ' WHERE pl.id=' . $id;
        else if ($packageOnly == 2)
            $query .= ' WHERE b.id=' . $id;
        $query .= ' and a.id in (select max(id) from addresses group by warehouse_id)';
        $query .= ' and p.deleted_at is null ';
        //$this->info($query);
        //return;

        $packages = DB::select($query);

        if (count($packages) <= 0) {
            echo "Error: Package does not exists\n";
            return;
        }

        if ($htmlFormat) {
            if ($packageOnly == 1)
                echo "<h3>Reset PACKAGE carriers posts</h3><br>\n";
            else if ($packageOnly == 0)
                echo "<h3>Reset PARCEL carriers posts</h3><br>\n";
            else if ($packageOnly == 2)
                echo "<h3>Reset BAG carriers posts</h3><br>\n";
            echo "<table class='tdep'>\n";
            echo "<tr>\n";
            echo "<th>No</th>\n";
            echo "<th>Time</th>\n";
            echo "<th>ID</th>\n";
            echo "<th>FIN</th>\n";
            echo "<th>Tracking Nunmber</th>\n";
            echo "<th>Result</th>\n";
            echo "</tr>\n";
        }
        //$package=$packages[0];
        $pn = 0;
        foreach ($packages as $package) {
            $_package = Package::find($package->id);
            $pn++;
            $ldate = date('Y-m-d H:i:s');
            if ($htmlFormat) {
                echo "<tr>\n";
                echo "<td>" . $pn . "</td>\n";
                echo "<td>" . $ldate . "</td>\n";
                echo "<td>" . $package->id . "</td>\n";
                echo "<td>" . $package->fin . "</td>\n";
                echo "<td>" . $package->custom_id . "</td>\n";
            }

            if (empty($package->fin)) {
                echo "<td>Error: fin code of package owner is empty</td>\n";
                echo "</tr>\n";
                continue;
            }

            if (!$deleteOnly) {

                if (empty(trim($package->address))) {
                    echo "<td>Error: address of package owner is empty</td>\n";
                    echo "</tr>\n";
                    continue;
                }
                $numItems = $_package->getNumberItems();
                if (!$package->w_customs_auto_delcaration && (empty($numItems) || ($numItems <= 0))) {
                    echo "<td>Error: Package number of items is empty</td>\n";
                    echo "</tr>\n";
                    continue;
                }
                $shipAmount = $_package->getShippingAmount();
                if (!$package->w_customs_auto_delcaration && (empty($shipAmount) || ($shipAmount <= 0))) {
                    echo "<td>Error: Package shipping amount is empty</td>\n";
                    echo "</tr>\n";
                    continue;
                }
                $weight = $_package->getWeight();
                if (empty($weight) || ($weight <= 0)) {
                    echo "<td>Error: Package weight is empty</td>\n";
                    echo "</tr>\n";
                    continue;
                }
                /*if(($package->status !=0) && ($package->status !=1) )
                {
                    echo "<td>Error: Package status is not in Warehouse or Sent</td>\n";
                    echo "</tr>\n";
                    continue;
                }*/
                if ($package->u_is_commercial && empty($package->u_voen)) {
                    echo "<td>Error: Package user is commercial but have no VOEN</td>\n";
                    echo "</tr>\n";
                    continue;
                }
            }


            $cm->fin = $package->fin;
            $cm->isCommercial = $package->u_is_commercial;
            $cm->trackingNumber = $package->custom_id;
            //check in carriers posts
            if ($package->u_check_customs) {
                $cpost = $cm->get_carrierposts2();

                /*if($checkOnly)
                {
                    print_r($res);
                }*/

                if ($cpost->code == 200) {
                    if (!empty($cpost->inserT_DATE)) {
                        //echo $cpost->request;
                        //echo "<br>\n";
                        //echo $cpost->result;
                        //echo "<br>\n";

                        if (!$package->u_is_commercial && ($cpost->status>0) && !empty($cpost->ecoM_REGNUMBER)) {
                            echo "<td>Error: Package is already has declaration(carrierposts " . $cpost->ecoM_REGNUMBER . ") in customs system and cannot be removed</td>\n";
                            echo "</tr>\n";
                            if (!$checkOnly)
                                continue;
                        }
                        if (!$package->u_is_commercial && ($cpost->status>0) && (!empty($package->ecoM_REGNUMBER) && (strtotime($ldate) - strtotime($package->pc_created_at)) < 3600)) {
                            echo "<td>Error: Package is already has declaration(declarations  " . $package->ecoM_REGNUMBER . ") in customs system and cannot be removed</td>\n";
                            echo "</tr>\n";
                            if (!$checkOnly)
                                continue;
                        }
                        /*			if(!$package->u_is_commercial && !empty($cpost->ecoM_REGNUMBER_OLD))
                                    {
                                        echo "<td>Error: Package is already has declaration(carrierposts ".$cpost->ecoM_REGNUMBER_OLD.") in customs system and cannot be removed</td>\n";
                            echo "</tr>\n";
                                        if(!$checkOnly)
                                        continue;
                                    }*/
                        if (!$package->u_is_commercial && !empty($cpost->depesH_NUMBER)) {
                            echo "<td>Error: Packages is already has depesh in customs system and cannot be removed</td>\n";
                            echo "</tr>\n";
                            if (!$checkOnly)
                                continue;
                        }
                    }
                } else {
                    if (!$package->u_is_commercial && (!empty($package->ecoM_REGNUMBER) && (strtotime($ldate) - strtotime($package->pc_created_at)) < 3600)) {
                        echo "<td>Error: Packages is already has declaration in customs system and cannot be removed</td>\n";
                        echo "</tr>\n";
                        if (!$checkOnly)
                            continue;
                    }
                    if (!$package->u_is_commercial && !empty($package->depesH_NUMBER)) {
                        echo "<td>Error: Packages is already has depesh in customs system and cannot be removed</td>\n";
                        echo "</tr>\n";
                        if (!$checkOnly)
                            continue;
                    }
                }
                if ($checkOnly) {
                    echo "Ok to update\n";
                    //return;
                }
            }
            //-----------

            //Delete from customs system
            if (!$checkOnly) {
                if ($package->u_check_customs || $package->pc_check_customs) {
                    $res = $cm->delete_carriers();
                    if (!isset($res->code)) {
                        echo "<td>Error: Cannot remove package from customs system. Empty Reposnse</td>\n";
                        echo "</tr>\n";
                        continue;
                    }

                    if (($res->code != 200) && ($res->code != 400)) {
                        $cm->parse_error($res);
                        echo "<td>";
                        echo "Error: Cannot remove package from customs system(" . $res->code . "): ";
                        echo "    errorMessage: " . $cm->errorMessage;
                        echo "    validationError: " . $cm->validationError;
                        echo "</td>\n";
                        echo "</tr>\n";
                        continue;
                    }
                    if ($deleteOnly) {
                        echo "<td>Ok: Package removed from customs system.</td>\n";
                        echo "</tr>\n";
                    }
                }
                if (!empty($package->pc_id)) {
                    DB::delete("delete from package_carriers where id=?", [$package->pc_id]);
                    $package->pc_id = NULL;
                }
                if ($deleteOnly) {
                    continue;
                }
            }
            //---------

            //Add to customs system
            $pc_id = $package->pc_id;
            if (!$package->u_check_customs) {
                if (empty($pc_id)) {
                    DB::insert("insert into package_carriers (package_id,fin,trackingNumber,code,check_customs,created_at) values (?,?,?,0,0,?)"
                        , [$package->id, $package->fin, $package->custom_id, $ldate]);
                    echo "<td>Ok: Package's user is not for using customs system</td>\n";
                    echo "</tr>\n";
                }
                continue;
            }
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
            $countryCode = strtolower($package->w_code);

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
            if ($countryCode == 'uk') $countryCode = 'gb';
            if ($countryCode == 'uae') $countryCode = 'ae';
            //$this->info("------------------");
            //$this->info("id:".$package->id);
            $cm->get_carriers_goods($_package->customs_type_id, $TypeId, $TypeStr, $package->id);
            $fullName = $package->name;
            $surname = $package->surname;
            if (!empty($surname))
                $fullName .= ' ' . $surname;

            $whtsp = array("\r\n", "\n", "\r");
            $cm->direction = 1;
            $cm->trackinG_NO = $package->custom_id;
            $cm->transP_COSTS = $deliveryAmount;
            $cm->weighT_GOODS = $_package->getWeight();//weight_goods;
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

            if ($checkOnly) {
                $this->info($cm->get_carriers_json_str());
                continue;
            }

            $res = $cm->add_carriers();

            $ldate = date('Y-m-d H:i:s');

            if (!isset($res->code)) {
                sleep(2);
                $res = $cm->add_carriers();
                $ldate = date('Y-m-d H:i:s');
            }
            $pc_id = $package->pc_id;
            if (!empty($pc_id)) {
                $ones = DB::select("select id from package_carriers where id=" . $pc_id);
                if (count($ones) <= 0) {
                    $pc_id = null;
                } else {
                }

            }


            if (!isset($res->code)) {
                echo "<td>";
                echo "Error: Cannot add package to customs system: Empty reposnose\n";
                //echo "\n<br>\n";
                //echo "<pre>".$cm->get_carriers_html_str()."</pre>";
                echo "<td></tr>\n";

                $message = "🛑 Eror adding package to customs system <b>from Web</b>\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                $message .= "Error: Empty response\n";
                $message .= "phone: " . $package->phone . "\n";
                $message .= "address: " . $cm->idxaL_ADRESS . "\n";
                if ($sendTelegram) sendTGMessage($message);

                $cm->updateDB2($package->id, $package->fin, $package->custom_id, $ldate, 999);
                $this->info($cm->get_carriers_json_str());
                continue;
            }

            if (($res->code == 400) && isset($res->exception) && is_object($res->exception) && isset($res->exception->status) && $res->exception->status == 'error')
                $res->code = 888;
            $cm->updateDB2($package->id, $package->fin, $package->custom_id, $ldate, $res->code, $package->country_id, $cm->idxaL_NAME, $cm->ixraC_NAME);

            if ($res->code == 200) {
                echo "<td>Ok: Package added to customs system</td>\n";
                echo "</tr>\n";

                $message = "✅ Package added to customs system (" . $res->code . ") <b>from Web</b>\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                if ($sendTelegram) sendTGMessage($message);

            } else {

                $cm->parse_error($res);
                echo "<td>";
                echo "Error: Cannot add package to customs system: ";
                if (!empty($cm->errorMessage))
                    echo "    errorMessage: " . $cm->errorMessage;
                echo "    validationError: " . $cm->validationError;
                if (!empty($cm->validationError))
                    //echo "\n<br>\n";

                    $message = "🛑 Eror adding package to customs system (" . $res->code . ") <b>from Web</b>\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->u_customer_id . "'>" . $package->u_customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                if (!empty($cm->errorMessage))
                    $message .= "errorMessage: " . $cm->errorMessage . "\n";
                if (!empty($cm->validationError))
                    $message .= "validationError: " . $cm->validationError . "\n";
                if ($sendTelegram) sendTGMessage($message);

                //echo "<pre>".$cm->get_carriers_html_str()."</pre>";
                echo "<td></tr>\n";
            }
            //------------

        }
        echo "</table>\n";
    }
}
