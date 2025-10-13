<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use DB;
use Illuminate\Console\Command;

class CarriersCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:check {manual=0} {package=99} {package_id=0} {track_id=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check carriers from packages';

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
        $manual = $this->argument("manual");
        $package_id = $this->argument('package_id');
        $track_id = $this->argument('package_id');
        $timeToSleep = 5;
        $retryCount = 5;
        $timeToRun = 30 * 60 - ($retryCount + 2) * $timeToSleep;
        if ($packageOnly != 99)
            $timeToRun = 3600;
        $ldate = date('Y-m-d H:i:s');
        $begin_tm = time();
        $cm = new CustomsModel();
        $cm->retryCount = $retryCount;
        $cm->retrySleep = $timeToSleep;
        $query = 'SELECT pc.* from package_carriers pc';
	if(!$track_id) {
          $query .= ' LEFT OUTER join packages p on pc.package_id=p.id';
          $query .= ' LEFT OUTER JOIN users u on p.user_id=u.id';
	}
	if($track_id)
          $query .= ' LEFT OUTER join tracks t on pc.track_id=t.id';
        $query .= ' LEFT OUTER JOIN parcel_package pp on pp.package_id=p.id';
        $query .= ' LEFT OUTER JOIN parcels pl on pp.parcel_id=pl.id';
        $query .= ' WHERE  pc.code in(200,400)';
	if(!$track_id) {
          $query .= " and (u.check_customs=1)";
          $query .= ' and p.status in (0,1)';
          $query .= ' and p.deleted_at is null';
	}
	if($track_id)
          $query .= ' and t.deleted_at is null';
        if ($manual) {
            $query .= ' and  p.status != 3 and p.number_items_goods is not null and p.shipping_amount_goods is not null and p.weight_goods is not null and not exists (select * from parcels inner join parcel_package on parcels.id = parcel_package.parcel_id where p.id = parcel_package.package_id) and p.status = 0 and p.warehouse_id = 11 and p.deleted_at is null';
            //$query.=' and p.id in (138721,138737,138735,138722,138723,138727,138728,138729,138733,138734,138731,138738,138732,138741,138740,138742,138743,138747,138748,138749,138750)';
        }
        $queryOne = $query;
        if ($packageOnly != 99) {
            if ($packageOnly) {
		if($track_id)
                  $query .= ' and t.id=' . $track_id;
		else
                  $query .= ' and p.id=' . $package_id;
	    } else
                $query .= ' and pl.id=' . $package_id;
        }
//	$query.=" and ((pc.depesH_NUMBER is null) or ((pc.depesH_NUMBER is not null) and (TIME_TO_SEC(TIMEDIFF('".$ldate."',pc.created_at))>6*3600)))";
        $query .= ' ORDER BY pc.created_at';
        if ($manual) {
            $query .= ' limit 1000 offset 95';
        }
        echo $query;
        //return;
        $packages = DB::select($query);
        $this->info($ldate . "  ===== Started to check carriers (" . count($packages) . " packages)=====");
        /*$this->info($ldate."  getting all posts...");
        $cm->get_carrierposts2();
        $ldate = date('Y-m-d H:i:s');
        $this->info($ldate."  Ok c_posts=".count($cm->c_posts)." d_posts=".count($cm->d_posts));*/
        $pn = 0;

        foreach ($packages as $package) {
            //print_r($package);
            $now_tm = time();
            if (($now_tm - $begin_tm) >= $timeToRun) {
                $this->info($ldate . "  ===== Timeout =====");
                return;
            }

            if ($pn > 0)
                sleep($timeToSleep);

            $pn++;

            $ones = DB::select($queryOne . " and pc.id=" . $package->id);
            if (count($ones) <= 0) {
                $this->info($ldate . "    Error: DB changed ");
                $package = null;
                continue;
            }
            $package = $ones[0];

            $cm->pinNumber = $package->fin;
            $cm->trackingNumber = $package->trackingNumber;
            $cm->isCommercial = $package->is_commercial;
            $ldate = date('Y-m-d H:i:s');
            $this->info($ldate . " " . $pn . "  Package: " . $package->package_id . " fin:" . $package->fin . " trackNo:" . $package->trackingNumber);
            $cpost = $cm->get_carrierposts2();

            $ones = DB::select($queryOne . " and pc.id=" . $package->id);
            if (count($ones) <= 0) {
                $this->info($ldate . "    Error: DB changed ");
                $package = null;
                continue;
            }
            $package = $ones[0];

            $ldate = date('Y-m-d H:i:s');
            /*for($retry=1;$retry<=$retryCount;$retry++)
            {
                if($cpost->code==999)
                  {
                    $this->info($ldate."    Error: Empty response (retry ".$retry.")");
                    sleep($timeToSleep);

                $ones=DB::select($queryOne." and pc.id=".$package->id);
                if(count($ones)<=0) {
                    $this->info($ldate."    Error: DB changed ");
                    $package=null;
                    break;
                }
                $package=$ones[0];

                $date1=$package->inserT_DATE;
                if(empty($date1) || ($date1=='0000-00-00 00:00:00'))
                {
                        $date1=$package->created_at;
                }
                $cm->pinNumber=$package->fin;
                $cm->trackingNumber=$package->trackingNumber;

                    $cpost=$cm->get_carrierposts2($date1);
                    $ldate = date('Y-m-d H:i:s');
                }
                else break;
            }*/
            if (!$package)
                $continue;

            if ($cpost->code == 999) {
                $this->info($ldate . "    Error: Empty response ");
                DB::update("update package_carriers set errorMessage=?,validationError=?,created_at=? where id=?"
                    , ['Empty response', '', $ldate, $package->id]);
                continue;
            }
            //        $this->info($cm->get_carriersposts_json_str());

            if ($cpost->code != 200) {
                $this->info($ldate . "    Error");
                $this->info("    errorMessage: " . $cpost->errorMessage);
                $this->info("    validationError: " . $cpost->validationError);
                $this->info("  ----*******----- ");
                $this->info($cpost->request);
                $this->info("  --------- ");
                $this->info($cpost->result);
                $this->info("  ----*******----- ");
                DB::update("update package_carriers set errorMessage=?,validationError=?,created_at=? where id=?"
                    , [$cpost->errorMessage, $cpost->validationError, $ldate, $package->id]);
            }
            // print_r($res);

            if ($cpost->code == 200) {
                if (!empty($cpost->inserT_DATE)) {
                    if ($cpost->ecoM_REGNUMBER != $cpost->ecoM_REGNUMBER_OLD)
                        $this->info($ldate . "       Warning  carrierposts regNumber:" . $cpost->ecoM_REGNUMBER_OLD . " != declarations RegNumber:" . $cpost->ecoM_REGNUMBER);

                    //$this->info($ldate."      Ok update regNumber:".$cpost->ecoM_REGNUMBER." depeshNumber:".$cpost->depesH_NUMBER." cost:".$cpost->cost);
                    $this->info($ldate . "      Ok update regNumber:" . $cpost->ecoM_REGNUMBER . " depeshNumber:" . $cpost->depesH_NUMBER);

                    $str = "update package_carriers";
                    $str .= " set code=200,errorMessage=null,validationError=null";
                    $str .= " ,inserT_DATE=?,airwaybill=?";
                    $str .= " ,depesH_NUMBER=?,depesH_DATE=?";
                    $str .= " ,status=?";//,ecoM_REGNUMBER=?";
                    //$str.=" ,cost=?,cost_usd=?,currency=?";
                    $str .= " ,created_at=?";
                    $str .= " where id=?";

                    /*if(empty($cpost->inserT_DATE))
                           $cpost->inserT_DATE=NULL;
                   if(empty($cpost->airwaybill))
                           $cpost->airwaybill=NULL;
                   if(empty($cpost->depesH_NUMBER))
                           $cpost->depesH_NUMBER=NULL;
                   if(empty($cpost->depesH_DATE))
                           $cpost->depesH_DATE=NULL;
                   if(empty($cpost->status))
                           $cpost->status=NULL;
                   if(empty($cpost->ecoM_REGNUMBER))
                       $cpost->ecoM_REGNUMBER=NULL;*/

                    DB::update($str, [
                        $cpost->inserT_DATE, $cpost->airwaybill
                        , $cpost->depesH_NUMBER, $cpost->depesH_DATE
                        , $cpost->status//,$cpost->ecoM_REGNUMBER
                        //,$cpost->cost,$cpost->costUSD,$cpost->currency
                        , $ldate
                        , $package->id]);
                } else {
                    $this->info($ldate . "    Ok not exists");
                    if (!empty($package->id) && empty($package->inserT_DATE)) {
                        DB::delete('delete from package_carriers where id=?', [$package->id]);
                    }
                    /*if(!empty($package->inserT_DATE))
                    {
                        $res=$cm->delete_carriers();
                            if(!isset($res->code))
                            {
                             $this->info($ldate."    Error(delete): Empty response ");
                                if($res->code != 200)
                                {
                                $cm->parse_error($res);
                                $this->info($ldate."    Error");
                                $this->info("    errorMessage: ".$cm->errorMessage);
                                $this->info("    validationError: ".$cm->validationError);
                                }
                        }
                    }*/
                }
            }
        }
        //
    }
}
