<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Extra\Notification;
use App\Models\Track;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Console\Command;

class CarriersTrackUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers_track:update  {package} {track_id} {checkonly=1} {htmlformat=0} {deleteonly=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update carriers from tracks';

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
        $id = $this->argument('track_id');

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

        $query = 'SELECT';
        $query .= ' t.id';
        $query .= ' ,pc.id as pc_id,pc.code as pc_code';
        $query .= ',pc.fin as pc_fin,pc.inserT_DATE,pc.created_at as pc_created_at,pc.ecoM_REGNUMBER,pc.depesH_NUMBER,pc.check_customs as pc_check_customs';
        $query .= ' FROM tracks t';
        $query .= ' left outer join package_carriers pc on pc.track_id=t.id';
        $query .= ' WHERE (t.deleted_at is null)';
        if ($packageOnly == 1)
            $query .= ' AND t.id=' . $id;
        else if ($packageOnly == 0)
            $query .= ' AND t.container_id=' . $id;
        else if ($packageOnly == 2)
            $query .= ' AND t.airbox_id=' . $id;
        //$query.=" and (t.created_at >= '2024-07-01 00:00:00')))";
        $query .= ' ORDER BY t.created_at';
        $queryOne = $query;
        $pn = 0;
        $items = DB::select($query);
        if (count($items) <= 0) {
            echo "Error: Track does not exists\n";
            return;
        }

        if ($htmlFormat) {
            if ($packageOnly == 1)
                echo "<h3>Reset Track carriers posts</h3><br>\n";
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
        foreach ($items as $item) {

            $track = Track::find($item->id);

            if ($track->customer && $track->customer->fin && $track->customer->fin != '-') {
                $cm->fin = $track->customer->fin;
            } elseif ($track->fin && $track->fin != '-') {
                $cm->fin = $track->fin;
            } else {
                $cm->fin = null;
            }
            $cm->isCommercial = 0;
            $cm->trackingNumber = $track->tracking_code;
            //print_r($track);
            //continue;

            if ($htmlFormat) {
                echo "<tr>\n";
                echo "<td>" . $pn . "</td>\n";
                echo "<td>" . $ldate . "</td>\n";
                echo "<td>" . $track->id . "</td>\n";
                echo "<td>" . $cm->fin . "</td>\n";
                echo "<td>" . $cm->trackingNumber . "</td>\n";
            }

            $pn++;
            $ldate = date('Y-m-d H:i:s');

            $now_tm = time();
            if (($now_tm - $begin_tm) >= $timeToRun) {
                echo "<td>  ===== Timeout =====</td>\n";
                echo "</tr>\n";
                return;
            }

            if (empty($cm->fin)) {
                echo "<td>Error: fin code is empty</td>\n";
                echo "</tr>\n";
                continue;
            }

            if (strlen($cm->fin) > 9 || strlen($cm->fin) < 5) {
                echo "<td>Error: Wrong fin length</td>\n";
                echo "</tr>\n";
                continue;
            }

            if ($pn > 0)
                sleep(3);


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

                    if (!empty($cpost->ecoM_REGNUMBER)) {
                        echo "<td>Error: Track is already has declaration(carrierposts " . $cpost->ecoM_REGNUMBER . ") in customs system and cannot be removed</td>\n";
                        echo "</tr>\n";
                        if (!$checkOnly)
                            continue;
                    }
                    if (!empty($track->ecoM_REGNUMBER) && (strtotime($ldate) - strtotime($item->pc_created_at)) < 3600) {
                        echo "<td>Error: Package is already has declaration(declarations  " . $item->ecoM_REGNUMBER . ") in customs system and cannot be removed</td>\n";
                        echo "</tr>\n";
                        if (!$checkOnly)
                            continue;
                    }

                    if (!empty($cpost->depesH_NUMBER)) {
                        echo "<td>Error: Packages is already has depesh in customs system and cannot be removed</td>\n";
                        echo "</tr>\n";
                        if (!$checkOnly)
                            continue;
                    }
                }
            } else {
                if (!empty($track->ecoM_REGNUMBER) && (strtotime($ldate) - strtotime($track->pc_created_at)) < 3600) {
                    echo "<td>Error: Packages is already has declaration in customs system and cannot be removed</td>\n";
                    echo "</tr>\n";
                    if (!$checkOnly)
                        continue;
                }
                if (!empty($track->depesH_NUMBER)) {
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

            //Delete from customs system
            if (!$checkOnly) {
                if ($item->pc_check_customs) {

                    $res = $cm->delete_carriers();

                    if (!isset($res->code)) {
                        echo "<td>Error: Cannot remove track from customs system. Empty Reponse</td>\n";
                        echo "</tr>\n";
                        continue;
                    }

                    if (($res->code != 200) && ($res->code != 400)) {
                        $cm->parse_error($res);
                        echo "<td>";
                        echo "Error: Cannot remove track from customs system(" . $res->code . "): ";
                        echo "    errorMessage: " . $cm->errorMessage;
                        echo "    validationError: " . $cm->validationError;
                        echo "</td>\n";
                        echo "</tr>\n";
                        continue;
                    }
                    if ($deleteOnly) {
                        echo "<td>Ok: Track removed from customs system.</td>\n";
                        echo "</tr>\n";
                    }
                }
                if (!empty($track->pc_id)) {
                    DB::delete("delete from package_carriers where id=?", [$track->pc_id]);
                    $track->pc_id = NULL;
                }
                if ($deleteOnly) {
                    continue;
                }
            }
            //---------

            //Add to customs system
            $pc_id = $item->pc_id;
            if (!empty($pc_id)) {
                $ones = DB::select($queryOne . " and pc.id=" . $pc_id);
                if (count($ones) <= 0) {
                    echo "<td>Error: db changed</td>\n";
                    echo "</tr>\n";
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
            if (empty($cm->fin) || empty(trim($address)) || !array_key_exists($countryCode, $cm_countries) || !$track->weight || !$track->number_items/*|| ($package->u_is_commercial && empty($package->u_voen))*/) {
                if (empty($cm->fin)) {
                    echo "<td>Error: Empty fin</td>\n";
                    echo "</tr>\n";
                }
                if (!$track->weight) {
                    echo "<td>Error: Empty weight</td>\n";
                    echo "</tr>\n";
                }
                if (!$track->number_items) {
                    echo "<td>Error: Empty number_items</td>\n";
                    echo "</tr>\n";
                }
                if (empty(trim($address))) {
                    echo "<td>Error: Empty address</td>\n";
                    echo "</tr>\n";
                }
                if (!array_key_exists($countryCode, $cm_countries)) {
                    echo "<td>Error: Wrong country code: " . $countryCode . "</td>\n";
                    echo "</tr>\n";
                }
                /*if ($package->u_is_commercial && empty($package->u_voen)) {
                    $this->info("    Commercial user has no voen");
                    $message .= "Commercial user has no voen\n";
                    $validationError = "Empty VOEN";
		}*/
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
            //echo "DPC:".$track->delivery_price_cur."\n";
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
            if ($checkOnly) {
                $this->info($cm->get_carriers_json_str());
                continue;
            }

            $pc_id = $item->pc_id;

            $res = $cm->add_carriers();
            $ldate = date('Y-m-d H:i:s');

            if (!isset($res->code)) {
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
                $this->info($cm->get_carriers_json_str());

                echo "<td>Error:  Cannot add track to customs system: Empty reposnose </td>\n";
                echo "</tr>\n";
                $message = "🛑 Eror adding track to customs system <b>from Web</b>\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber . "</a>\n";
                $message .= "Error: Empty response\n";
                $message .= "phone: " . $cm->phone . "\n";
                $message .= "address: " . $cm->idxaL_ADRESS . "\n";
                //$message.= $cm->get_carriers_html_str();
                //$message.=$cm->get_carriers_html_str();
                if ($sendTelegram) sendTGMessage($message);
                $cm->updateTrackDB2($track->id, $cm->fin, $cm->trackingNumber, $ldate, 999);
                if (in_array($track->status, [1, 2, 5])) {
                    $track->status = 6;
                    $track->save();
                    (new PackageService())->updateStatus($track, 6);
                }
                continue;
            }
            //print_r($res);
            if (($res->code == 400) && isset($res->exception) && is_object($res->exception) && isset($res->exception->status) && $res->exception->status == 'error')
                $res->code = 888;
            $cm->updateTrackDB2($track->id, $cm->fin, $cm->trackingNumber, $ldate, $res->code, NULL, $cm->idxaL_NAME, $cm->ixraC_NAME);
            if ($res->code == 200) {
                echo "<td>Ok: Track added to customs system</td>\n";
                echo "</tr>\n";
                $message = "✅ Track added to customs system (" . $res->code . ") <b>from Web</b>\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber . "</a>\n";
                if ($sendTelegram) sendTGMessage($message);
                if (in_array($track->status, [1, 2, 6])) {
                    $track->status = 5;
                    $track->save();
                    Notification::sendTrack($track->id, 5);
                    (new PackageService())->updateStatus($track, 5);
                }

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
                echo "<td>";
                echo "Error: Cannot add track to customs system1: ";
                if (!empty($errorMessage))
                    echo "errorMessage: " . $errorMessage;
                if (!empty($validationError))
                    echo "validationError: " . $validationError;
                echo "<td></tr>\n";
                $message = "🛑 Eror adding track to customs system (" . $res->code . ")\n";
                $message .= "<b>" . $fullName . "</b>";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/tracks?q=" . $cm->trackingNumber . "'>" . $cm->trackingNumber . "</a>\n";
                if (!empty($errorMessage))
                    $message .= "errorMessage: " . $errorMessage . "\n";
                if (!empty($validationError))
                    $message .= "validationError: " . $validationError . "\n";
                //$message.=$cm->get_carriers_html_str();
                if ($sendTelegram) sendTGMessage($message);
                //$this->info("Telegram message: $message");
                //$this->info("Telegram result: $gt_res");
                if (in_array($track->status, [1, 2])) {
                    $track->status = 6;
                    $track->save();
                    (new PackageService())->updateStatus($track, 6);
                }
            }
        }
        echo "</table>\n";
        //
    }
}
