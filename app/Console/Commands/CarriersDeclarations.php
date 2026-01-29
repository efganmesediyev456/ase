<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\CustomsType;
use App\Models\Package;
use App\Models\Track;
use App\Models\PackageGood;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Console\Command;
use App\Services\Integration\UnitradeService;

class CarriersDeclarations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:declarations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update declarations info';

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
        $ldate = date('Y-m-d H:i:s');
        echo $ldate . "\n";

        $cnt = 0;
        $cm = new CustomsModel();
        $cm->retryCount = 5;
        $cm->retrySleep = 4 * 60 + 2;
        $cm->dateTo = date('Y-m-d H:i:s');
	$nightMode=true;
	$h=(int)date('H');
	if($h <= 18 || $h >= 22) {
	    $nightMode=false;
	}
	if(!$nightMode) {
	    if($h<=12)
	        $cm->dateFrom = date('Y-m-d H:i:s',strtotime("-20 hour"));
	    else
	        $cm->dateFrom = date('Y-m-d H:i:s',strtotime("-4 hour"));
	    echo " Day mode: ".$cm->dateFrom." - ".$cm->dateTo."\n";
	} else {
	    $cm->dateFrom = date('Y-m-d H:i:s',strtotime("-10 day"));
	    echo " Night mode: ".$cm->dateFrom." - ".$cm->dateTo."\n";
	}
        $offset = 0;
        $intervalTotal = 4 * 60 + 2;
        $intervalPackage = 2;
        $offset_file = "/tmp/ase_declarations_offset.txt";
        $cnt = 0;
        //$pkg_list = '';
        $success_count = 0;
        while (true) {
            $ldate = date('Y-m-d H:i:s');
            $intervalRequest = $intervalTotal;
            //echo $ldate."  "."\n";
            /*if(file_exists($offset_file)) {
                $offset=chop(file_get_contents($offset_file));
            }
            if(!$offset) $offset=0;*/
            $arr = $cm->declarations2($offset);
            echo $ldate . "  " . $offset . "\n";
            if (!$arr) {
                echo "Exiting due to errors\n";
                return;
            }
            //print_r($arr);
            //echo count($arr)."\n";
            if (count($arr) <= 0) {
                //error
                //$offset=0;
                //file_put_contents($offset_file,$offset);
                break;
            }
            $success_count++;
            $currencies2 = config('ase.attributes.currencies2');
            $customsCurrencies = config('ase.attributes.customsCurrencies');
            foreach ($arr as $dec) {
                $cnt++;
                $ldate = date('Y-m-d H:i:s');
                echo "  " . $cnt . " " . $ldate . " " . $dec->trackingNumber . " " . $dec->regNumber;

                $str = "select pc.id,pc.package_id,pc.track_id,pc.ecoM_REGNUMBER,pc.cost,pc.cost_usd,pc.currency,pc.insert_date_dec from package_carriers pc";
                //$str.=" where pc.package_id=(select p.id from packages p where p.deleted_at is null and p.custom_id='".$dec->trackingNumber."')";
                $str .= " where pc.deleted_at is null and pc.trackingNumber='" . $dec->trackingNumber . "'";
                $pkgs = DB::select($str);
                if (count($pkgs) <= 0) {
                    //echo "  ".$cnt." ".$ldate." ".$dec->trackingNumber." ".$dec->regNumber."     not exists\n";
                    echo "     not exists\n";
                    sleep($intervalPackage);
                    $intervalRequest -= $intervalPackage;
                    continue;
                }
                $pkg = $pkgs[0];

                $cost = 0;
                $costUSD = 0;
                $currency = 0;
                if (isset($dec->goodsList) && is_array($dec->goodsList) && count($dec->goodsList) > 0) {
                    foreach ($dec->goodsList as $good) {
                        $currencyType = $good->currencyType;
                        $invoicePrice = $good->invoicePrice;
                        $invoicePriceUSD = $good->invoicePriceUsdNumber;
                        $costUSD += $invoicePriceUSD;
                        if (!$currency)
                            $currency = $currencyType;
                        if ($currencyType == $currency)
                            $cost += $invoicePrice;
                        else {
                            $cost += number_format(0 + round($invoicePrice * getCustomsCurrencyRate($currencyType) / getCustomsCurrencyRate($currency), 2), 2, ".", "");
                        }
                    }
                }

                $pkg_cost = number_format(0 + round($pkg->cost, 2), 2, ".", "");
                $pkg_costUSD = number_format(0 + round($pkg->cost_usd, 2), 2, ".", "");

                $dec_cost = number_format(0 + round($cost, 2), 2, ".", "");
                $dec_costUSD = number_format(0 + round($costUSD, 2), 2, ".", "");

                //if (empty($pkg_list)) $pkg_list = $pkg->id;
                //else $pkg_list .= ',' . $pkg->id;

                if ($pkg->ecoM_REGNUMBER == $dec->regNumber && $pkg_cost == $dec_cost && $pkg_costUSD == $dec_costUSD && $pkg->currency == $currency) {// && $pkg->insert_date_dec==$dec->insertDate) {
                    //$ldate = date('Y-m-d H:i:s');
                    //echo "  ".$cnt." ".$ldate." ".$dec->trackingNumber." ".$dec->regNumber." ".$cost." ".$costUSD." ".$currency."\n";
                    sleep($intervalPackage);
                    $intervalRequest -= $intervalPackage;
                    $str = "update package_carriers set created_at=? where id=?";
                    DB::update($str, [$ldate, $pkg->id]);
                    echo "  " . $pkg->ecoM_REGNUMBER . " = " . $dec->regNumber . " " . $cost . "[" . $pkg_cost . "] " . $costUSD . "[" . $pkg_costUSD . "] " . $currency . "[" . $pkg->currency . "] " . $dec->insertDate . " \n";
                    continue;
                }


                $str = "update package_carriers";
                $str .= " set ecoM_REGNUMBER=?";
                $str .= " ,cost=?,cost_usd=?,currency=?";
                $str .= " ,insert_date_dec=?";
                $str .= " ,created_at=?";
                $str .= " ,status=1";
                $str .= " where id=?";

                DB::update($str, [
                    $dec->regNumber
                    , $cost, $costUSD, $currency
                    , $dec->insertDate
                    , $ldate
                    , $pkg->id]);

                //echo "  ".$cnt." ".$ldate." ".$dec->trackingNumber." [".$dec->regNumber."][".$pkg->ecoM_REGNUMBER."] [".$cost."][".$pkg_cost."] [".$costUSD."][".$pkg_costUSD."] [".$currency."][".$pkg->currency."] ".$dec->insertDate." updated\n";
                echo "  " . $pkg->ecoM_REGNUMBER . " => " . $dec->regNumber . " " . $cost . "[" . $pkg_cost . "] " . $costUSD . "[" . $pkg_costUSD . "] " . $currency . "[" . $pkg->currency . "] " . $dec->insertDate . " updated\n";

		$_package=NULL;
		$_track=NULL;
		if($pkg->package_id)
                   $_package = Package::find($pkg->package_id);
		if($pkg->track_id)
                   $_track = Track::find($pkg->track_id);
                //if ($_package && $_package->warehouse && $_package->warehouse->customs_auto_delcaration) {
                if ($_package && !$_package->shipping_amount && !$_package->declaration && !$_package->number_items) {
                    $detailedType = [];
                    PackageGood::where('package_id', $_package->id)->delete();
                    if (isset($dec->goodsList) && is_array($dec->goodsList) && count($dec->goodsList) > 0) {
                        $currencyASE = 0;
                        $quantityASE = 0;
                        foreach ($dec->goodsList as $good) {
                            $currencyType = $good->currencyType;
                            $invoicePrice = $good->invoicePrice;
                            $invoicePriceUSD = $good->invoicePriceUsdNumber;
                            $quantityUnit = $good->quantityUnit;
                            $quantity = 0;
                            if (!$quantityUnit) {
                                $quantity = $good->quantity;
                                $quantityASE += $quantity;
                            }
                            if (!$currency) {
                                $currency = $currencyType;
                            }
                            if (isset($customsCurrencies[$currency])) {
                                $currencyASE = $currencies2[$customsCurrencies[$currency]];
                            }


                            $packageGood = new PackageGood;
                            $customsType = null;
                            if (isset($good->subGoodsUnicCode)) {
                                $packageGood->customs_type_id = $good->subGoodsUnicCode;
                                $customsType = CustomsType::find($packageGood->customs_type_id);
                            }
                            if (isset($good->mainGoodsGroupUnicCode))
                                $packageGood->customs_type_parent_id = $good->mainGoodsGroupUnicCode;
                            $typeName = '-';
                            if ($customsType)
                                $typeName = $customsType->name_en_with_parent;
                            $detailedType[] = $quantity . " x " . $typeName;
                            $packageGood->package_id = $_package->id;
                            $packageGood->number_items = $quantity;
                            $packageGood->shipping_amount = $invoicePrice;
                            $packageGood->shipping_amount_usd = $invoicePriceUSD;
                            if (isset($customsCurrencies[$currencyType])) {
                                $packageGood->shipping_amount_cur = $currencies2[$customsCurrencies[$currencyType]];
                            }
                            $packageGood->country_id = $_package->country_id;
                            $packageGood->warehouse_id = $_package->warehouse_id;
                            $packageGood->save();

                        }
                        $_package->shipping_amount = $cost;
                        $_package->shipping_amount_goods = $cost;
                        $_package->shipping_amount_usd = $costUSD;
                        $_package->shipping_amount_cur = $currencyASE;
                        $_package->number_items = $quantityASE;
                        $_package->number_items_goods = $quantityASE;
                        $_package->detailed_type = implode("; ", $detailedType);
                        $_package->declaration = 1;
                        $_package->save();

                        echo "    " . $_package->detailed_type . "\n";
                    }
                }
		if($_track) {
		   if($_track->status <7 && !empty($dec->regNumber)) {
			$_track->status=7;
			$_track->declaration=1;
			$_track->save();
			(new PackageService())->updateStatus($_track, 7);
		   }elseif ($_track->status == 18){
               $_track->declaration=1;
               $_track->bot_comment = "The track is currently in Customs and has been declared";
               $_track->save();
           }
		}
                sleep($intervalPackage);
                $intervalRequest -= $intervalPackage;
            }
            if (count($arr) < 100) {
                //end
                //$offset=0;
                //file_put_contents($offset_file,$offset);
                break;
            }
            $offset += 100;
            //file_put_contents($offset_file,$offset);
            sleep($intervalRequest);
        }
	/*
        if ($nightMode && $success_count >= 5) {
            //$str="select p.id,p.custom_id,pc.ecoM_REGNUMBER,current_timestamp,pc.created_at,pc.insert_date_dec from packages p left outer join package_carriers pc on p.id=pc.package_id";
	    $str = "select pc.id,pc.fin,pc.is_commercial,pc.trackingNumber,pc.ecoM_REGNUMBER,current_timestamp,pc.created_at,pc.insert_date_dec from package_carriers pc";
	    $str.= " left outer join  packages p on p.id=pc.package_id";
	    $str.= " left outer join  tracks t on t.id=pc.track_id";
            //$str.=" where p.deleted_at is null and p.status in (0,1,6) and pc.ecoM_REGNUMBER is not null and TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>=3600";
            //$str.=" where p.deleted_at is null and p.status in (0,1,6) and pc.ecoM_REGNUMBER is not null";
	    $str .= " where (p.id is null or (p.id is not null and p.deleted_at is null and p.status in (0,1,6)))";
	    $str .= " and (t.id is null or (t.id is not null and t.deleted_at is null and t.status < 16 ))";
	    $str .= " and pc.ecoM_REGNUMBER is not null";
            $str .= " and pc.deleted_at is null and pc.depesH_NUMBER is null and pc.status=1";
            //$str.=" and TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>=6*3600 and pc.id not in (".$pkg_list.")";
            //$str.=" and TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>=30*86400 and pc.id not in (".$pkg_list.")";
            $str .= " and pc.id not in (" . $pkg_list . ")";
            //echo $str."\n";
            $pkgs = DB::select($str);
            $cnt = 0;
	    echo "checking ".count($pkgs)."...\n";
            foreach ($pkgs as $pkg) {
                $ldate = date('Y-m-d H:i:s');
                $cnt++;
                $cm->pinNumber = $pkg->fin;
                $cm->isCommercial = $pkg->is_commercial;
                $cm->trackingNumber = $pkg->trackingNumber;
                $cpost = $cm->get_carrierposts2();
                echo "  " . $cnt . " " . $ldate . " " . $pkg->trackingNumber . " " . $pkg->ecoM_REGNUMBER . " " . $pkg->created_at;
                if ($cpost->code == 200 && !$cpost->status) {
                    $str = "update package_carriers set ecoM_REGNUMBER=null,cost=0,cost_usd=0,currency=0,insert_date_dec=NULL where id=?";
                    DB::update($str, [$pkg->id]);
                    echo " cleared";
                }
		echo "\n";
                sleep(1);
            }
	}*/
	echo "Completed\n";
    }
}
