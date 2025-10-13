<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Package;
use DB;
use Illuminate\Console\Command;

class CarriersList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:list {package} {declarations} {custom_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List carriers/declarations from packages';

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
        $packageOnly = $this->argument("package");
        $id = $this->argument('custom_id');
        $declarations = $this->argument('declarations');
        $timeToSleep = 1;
        $retryCount = 10;
        $timeToRun = 15 * 60 - ($retryCount + 1) * $timeToSleep;
        if ($packageOnly != 99)
            $timeToRun = 3600;
        $ldate = date('Y-m-d H:i:s');
        $cm = new CustomsModel();
        $cm->retryCount = $retryCount;
        $cm->retrySleep = $timeToSleep;
        $query = 'SELECT pc.* from package_carriers pc';
        $query .= ' LEFT OUTER join packages p on pc.package_id=p.id';
        $query .= ' LEFT OUTER join  tracks t on pc.track_id=t.id';
        $query .= ' LEFT OUTER JOIN parcel_package pp on pp.package_id=p.id';
        $query .= ' LEFT OUTER JOIN parcels pl on pp.parcel_id=pl.id';
        $query .= ' WHERE ';
        $query .= ' pc.code in(200,400)';
//	$query.=' and p.status in (0,1)';
        $query .= ' and ((p.id is not null and p.deleted_at is null) or (t.id is not null and t.deleted_at is null))';
        if ($packageOnly == 2) {
            $query .= " and pc.trackingNumber='" . $id . "'";
        } else if ($packageOnly != 99) {
            if ($packageOnly)
                $query .= " and p.custom_id='" . $id . "'";
            else
                $query .= " and pl.custom_id='" . $id . "'";
        } else {
            $query .= " and p.custom_id in ('ASE4004975281704','ASE8226256180582')";
        }
//	$query.=" and ((pc.depesH_NUMBER is null) or ((pc.depesH_NUMBER is not null) and (TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>6*3600)))";
        $query .= ' ORDER BY pc.created_at';
        echo $query;
        //return;
        $packages = DB::select($query);
        //$this->info($ldate."  ===== Started to check carriers (".count($packages)." packages)=====");
        $pn = 0;

        $begin_tm = time();

        $str = "No;Id;Fin;TrackNo";
        if ($declarations)
            $str .= ";regNumber;payStatus_Id;payStatus;receiveStatus;pinNumber;importName;phone;exportName;fromCountry;carrierVoen;insertDate;isUpdated;mainGoodsGroupUnicCode;subGoodsUnicCode;goodsName;quantityUnit;quantity;quantityFull;currencyType;invoicePrice;invoicePriceFull;invoicePriceUsd;invoicePriceUsdNumber;shippingCost";
        else
            $str .= ";idxaL_NAME;ixraC_NAME;transP_COSTS;weighT_GOODS;quantitY_OF_GOODS;invoyS_PRICE;invoyS_AZN;invoyS_USD;ecoM_REGNUMBER;depesH_NUMBER";
        $this->info($str);
        foreach ($packages as $package) {
            //print_r($package);
            $now_tm = time();
            /*if(($now_tm-$begin_tm)>=$timeToRun)
            {
                    $this->info($ldate."  ===== Timeout =====");
                return;
            }*/

            /*if($pn>0)
                sleep(5);*/

            $pn++;

            /*$date1=$package->inserT_DATE;
            if(empty($date1) || ($date1=='0000-00-00 00:00:00'))
            {
                $date1=$package->created_at;
            }*/
            $date1 = date('Y-m-d H:i:s');
            $cm->pinNumber = $package->fin;
            $cm->isCommercial = $package->is_commercial;
            $cm->trackingNumber = $package->trackingNumber;
            $cm->isDeleted = true;
	    $cm->status=0;
	    echo $cm->get_carriersposts_json_str()."\n";
            //$cm->dateFrom='2022-03-30 00:00:00';
            //$cm->dateTo='2022-04-05 00:00:00';
            //$cm->dateFrom='2022-06-13 00:00:00';
            //$cm->dateTo='2022-06-27 00:00:00';
            //$cm->status=1;
            $cm->packagE_TYPE = "Posts";
            $ldate = date('Y-m-d H:i:s');
            $res = [];
            //$this->info($ldate." ".$pn."  Package: ".$package->package_id." fin:".$package->fin." trackNo:".$package->trackingNumber);
            if ($declarations) {
                echo $cm->get_declarations_json_str();
                $res = $cm->get_declarations();
            } else
                $res = $cm->get_carrierposts();
            $ldate = date('Y-m-d H:i:s');

            echo "Cost:\n";
            $_package = NULL;
            if ($package->package_id) {
                $_package = Package::find($package->package_id);
                echo "Shipping price: " . $_package->shipping_price_customs . "\n";
                echo "Total price: " . $_package->total_price_customs . "\n";
            }
            //$cost=$cm->getCost();
            /*for($retry=1;$retry<=$retryCount;$retry++)
            {
                //echo ("*".$cm->get_carriersposts_json_str());
                    //print_r($res);
                //if(!isset($res->code) || !(isset($res->data) && is_array($res->data) && count($res->data)>0) )
                if(!isset($res->code))
                  {
            //        $this->info($ldate."    Error: Empty response (retry ".$retry.")");
                    sleep($timeToSleep);
                    if($declarations)
                       $res=$cm->get_declarations($date1);
                    else
                        $res=$cm->get_carrierposts($date1);
                    $ldate = date('Y-m-d H:i:s');
                }
                else break;
            }*/

            if (!isset($res->code))// || !(isset($res->data) && is_array($res->data) && count($res->data)>0))
            {
                $this->info($ldate . "    Error: Empty response ");
                $str = $pn . ";" . $package->package_id;
                $str .= ";" . $package->fin;
                $str .= ";" . $package->trackingNumber;
                if ($declarations)
                    $str .= ";;;;;;;;;;;;;;;;;;;;;;;;";
                else
                    $str .= ";;;;;;;;;;";
                $this->info($str);
                continue;
            }
            //        $this->info($cm->get_carriersposts_json_str());

            if ($res->code != 200) {
                $cm->parse_error($res);
                $this->info($ldate . "    Error");
                $this->info("    errorMessage: " . $cm->errorMessage);
                $this->info("    validationError: " . $cm->validationError);
                //$this->info("  ----*******----- ");
                //print_r($res);
                //$this->info("  --------- ");
                //$this->info($cm->get_carriersposts_json_str());
                //$this->info("  ----*******----- ");
                //DB::update("update package_carriers set code=?,errorMessage=?,validationError=? where id=?"
                //		, [$res->code,$cm->errorMessage,$cm->validationError,$package->id]);
            }
            //print_r($res);
            echo(json_encode($res, JSON_PRETTY_PRINT));

            if ($res->code == 200) {
                if (isset($res->data) && is_array($res->data) && count($res->data) > 0) {
                    $cpost = $res->data[0];
                    //$str="No;Id;Fin;TrackNo;idxaL_NAME;ixraC_NAME;transP_COSTS;weighT_GOODS;quantitY_OF_GOODS;invoyS_PRICE;invoyS_AZN;invoyS_USD;ecoM_REGNUMBER;depesH_NUMBER";
                    $str = $pn . ";" . $package->package_id;
                    $str .= ";" . $package->fin;
                    $str .= ";" . $package->trackingNumber;
                    $ecnt = 0;
                    if ($declarations) {
                        if (empty($cpost->regNumber)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->regNumber;
                        if (empty($cpost->payStatus_Id)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->payStatus_Id;
                        if (empty($cpost->payStatus)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->payStatus;
                        if (empty($cpost->receiveStatus)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->receiveStatus;
                        if (empty($cpost->pinNumber)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->pinNumber;
                        if (empty($cpost->importName)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->importName;
                        if (empty($cpost->phone)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->phone;
                        if (empty($cpost->exportName)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->exportName;
                        if (empty($cpost->fromCountry)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->fromCountry;
                        if (empty($cpost->carrierVoen)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->carrierVoen;
                        if (empty($cpost->insertDate)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->insertDate;
                        if (empty($cpost->isUpdated)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->isUpdated;

                        $cpost = $cpost->goodsList[0];

                        if (empty($cpost->mainGoodsGroupUnicCode)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->mainGoodsGroupUnicCode;
                        if (empty($cpost->subGoodsUnicCode)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->subGoodsUnicCode;
                        if (empty($cpost->goodsName)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->goodsName;
                        if (empty($cpost->quantityUnit)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->quantityUnit;
                        if (empty($cpost->quantity)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->quantity;
                        if (empty($cpost->quantityFull)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->quantityFull;
                        if (empty($cpost->currencyType)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->currencyType;
                        if (empty($cpost->invoicePrice)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoicePrice;
                        if (empty($cpost->invoicePriceFull)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoicePriceFull;
                        if (empty($cpost->invoicePriceUsd)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoicePriceUsd;
                        if (empty($cpost->invoicePriceUsdNumber)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoicePriceUsdNumber;
                        if (empty($cpost->shippingCost)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->shippingCost;
                    } else {
                        if (empty($cpost->idxaL_NAME)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->idxaL_NAME;
                        if (empty($cpost->ixraC_NAME)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->ixraC_NAME;
                        if (empty($cpost->transP_COSTS)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->transP_COSTS;
                        if (empty($cpost->weighT_GOODS)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->weighT_GOODS;
                        if (empty($cpost->quantitY_OF_GOODS)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->quantitY_OF_GOODS;
                        if (empty($cpost->invoyS_PRICE)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoyS_PRICE;
                        if (empty($cpost->invoyS_AZN)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoyS_AZN;
                        if (empty($cpost->invoyS_USD)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->invoyS_USD;
                        if (empty($cpost->ecoM_REGNUMBER)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->ecoM_REGNUMBER;
                        if (empty($cpost->depesH_NUMBER)) {
                            $str .= ";";
                            $ecnt++;
                        } else
                            $str .= ";" . $cpost->depesH_NUMBER;
                    }
                    $this->info($str);


                } else {
                    $str = $pn . ";" . $package->package_id;
                    $str .= ";" . $package->fin;
                    $str .= ";" . $package->trackingNumber;
                    if ($declarations)
                        $str .= ";;;;;;;;;;;;;;;;;;;;;;;;";
                    else
                        $str .= ";;;;;;;;;;";
                    $this->info($str);
                }
            }
        }
        //
    }
}
