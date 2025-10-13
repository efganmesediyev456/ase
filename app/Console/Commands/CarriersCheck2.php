<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use DB;
use Illuminate\Console\Command;
use App\Models\Track;
use App\Models\Package;
use App\Models\PackageCarrier;
use App\Services\Package\PackageService;

class CarriersCheck2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:check2  {--type=update_track_declaration} {--cwb=}';

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
        $cwb = $this->option('cwb');
        $type = $this->option('type');
        //$ldate = date('Y-m-d H:i:s',strtotime("-1 day"));
        //$this->info($ldate . "  ===== Started to check carriers packages) ".$type." =====");
	if ($type == 'update_track_declaration') {
	    $this->update_track_declaration($cwb);
	} else if ($type == 'clean_declaration') {
	    $this->clean_declaration($cwb);
	} else if ($type == 'update_commercial') {
	    $this->update_commercial_package($cwb);
	} else {
	    echo "Unknown type: ".$type."\n";
	}
    }

    public function update_commercial_package($cwb)
    {
        $timeToSleep = 10;
        $retryCount = 5;
        $timeToRun = 30 * 60 - ($retryCount + 2) * $timeToSleep;
        $ldate2 = date('Y-m-d H:i:s');
        $ldate1 = date('Y-m-d H:i:s',strtotime("-14 day"));
        $begin_tm = time();
        $cm = new CustomsModel();
	$cm->status=3;
        $cm->retryCount = $retryCount;
        $cm->retrySleep = $timeToSleep;
	$cm->isCommercial = true;
	if($cwb) {
	   $cm->trackingNumber = $cwb;
	} else {
	   $cm->dateFrom = $ldate1;
	   $cm->dateTo = $ldate2;
	}
        $posts = [];
        $posts = $cm->carriersposts2();
        $num = 0;
	if(count($posts) >0 ) {
            $ldate = date('Y-m-d H:i:s');
	    echo $ldate." Checking ".count($posts)." ...\n";
	}
	    //$tracks = Track::whereNull('tracks.deleted_at')->where('tracks.status', 5)->where('tracks.partner_id',8);
	$packages = Package::with(['parcels','bags','carrier'])->select('packages.*')->whereNull('packages.deleted_at');
	if($cwb) {
	    $packages = $packages->where('packages.custom_id',$cwb);
	}
	$packages = $packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id');
	$packages = $packages->leftJoin('parcel_package', 'packages.id', 'parcel_package.package_id');
	$packages = $packages->leftJoin('bag_package', 'packages.id', 'bag_package.package_id');
	$packages = $packages->leftJoin('parcels', 'parcels.id', 'parcel_package.parcel_id');
	$packages = $packages->leftJoin('bags', 'bags.id', 'bag_package.bag_id');
	$packages = $packages->whereIn('packages.status',[0,1,7]);
	$packages = $packages->where('package_carriers.is_commercial',1);
	$packages = $packages->where('package_carriers.status',3);
	$packages = $packages->whereRaw('((parcels.custom_id != package_carriers.airwaybill) or (bags.custom_id != package_carriers.depesH_NUMBER))');
	$packages = $packages->get();
        foreach ($packages as $package) {
	    $carrier=$package->carrier;
	    if(!$carrier)
		continue;
	    $parcels=$package->parcels;
	    $bags=$package->bags;
	    if($parcels || !$bags)
		continue;
	    $parcel=$parcels[0];
	    $bag=$bags[0];
	    if($carrier->airwaybill == $parcel->custom_id && $carrier->depesH_NUMBER == $bag->custom_id)
		continue;
            $num++;
            echo "$num " . $carrier->trackinG_NO . "  " . $carrier->depesH_NUMBER . " ".$carrier->aiwaybill. " ".$bag->custom_id." ".$parcel->custom_id;
	    echo "\n";
	    //$cm->updateDB(NULL, $track->fin, $track->tracking_code, $ldate2, $post);
        }
    }


    public function update_track_declaration($cwb)
    {
        $timeToSleep = 10;
        $retryCount = 5;
        $timeToRun = 30 * 60 - ($retryCount + 2) * $timeToSleep;
        $ldate2 = date('Y-m-d H:i:s');
        $ldate1 = date('Y-m-d H:i:s',strtotime("-1 day"));
        $begin_tm = time();
        $cm = new CustomsModel();
	$cm->status=1;
        $cm->retryCount = $retryCount;
        $cm->retrySleep = $timeToSleep;
	$cm->isCommercial = false;
	if($cwb) {
	   $cm->trackingNumber = $cwb;
	} else {
	   $cm->dateFrom = $ldate1;
	   $cm->dateTo = $ldate2;
	}
        $ldate = date('Y-m-d H:i:s');
	echo $ldate." Starting ". $cm->dateFrom." - ". $cm->dateTo." \n";
        $posts = [];
        $posts = $cm->carriersposts2();
        $num = 0;
	if(count($posts) >0 ) {
            $ldate = date('Y-m-d H:i:s');
	    echo $ldate." Checking ".count($posts)." ...\n";
	}
        foreach ($posts as $post) {
	    //$tracks = Track::whereNull('tracks.deleted_at')->where('tracks.status', 5)->where('tracks.partner_id',8);
	    $tracks = Track::whereNull('tracks.deleted_at')->whereIn('tracks.partner_id',[3,8,9]);
	    $tracks = $tracks->whereNull('package_carriers.deleted_at');
	    $tracks = $tracks->where('tracks.tracking_code',$post->trackinG_NO);
	    $tracks = $tracks->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id')->select('tracks.*', 'package_carriers.status as pc_status');
	    $tracks = $tracks->where('package_carriers.status',0);
	    $tracks = $tracks->whereNull('package_carriers.depesH_NUMBER');
	    $tracks = $tracks->get();
	    if(!$tracks || count($tracks)<=0) {
		 continue;
	    }
            $num++;
            echo "$num " . $post->trackinG_NO . "  " . $post->status . " ";
	    $track=$tracks[0];
	    $cm->updateDB(NULL, $track->fin, $track->tracking_code, $ldate2, $post);
	    if($track->status != 7) {
	       $track->status=7;
	       $track->save();
	       (new PackageService())->updateStatus($track, 7);
	       echo "updated\n";
	    }
        }
    }

    public function clean_declaration($cwb)
    {
        $timeToSleep = 10;
        $retryCount = 5;
        $timeToRun = 30 * 60 - ($retryCount + 2) * $timeToSleep;
        $ldate2 = date('Y-m-d H:i:s');
        $ldate1 = date('Y-m-d H:i:s',strtotime("-1 day"));
        $begin_tm = time();
        $cm = new CustomsModel();
	$cm->status=0;
        $cm->retryCount = $retryCount;
        $cm->retrySleep = $timeToSleep;
	$cm->isCommercial = false;
	if($cwb) {
	   $cm->trackingNumber = $cwb;
	} else {
	   //$cm->dateFrom = $ldate1;
	   //$cm->dateTo = $ldate2;
	}
	echo $ldate2." Starting ". " \n";
        $posts = [];
        $posts = $cm->carriersposts2();
        $num = 0;
        $num_cleaned = 0;
	if(count($posts) >0 ) {
            $ldate = date('Y-m-d H:i:s');
	    echo $ldate." Checking ".count($posts)."...\n";
	}
        foreach ($posts as $post) {
	    $num++;
            //echo $num." " . $post->trackinG_NO . "  " . $post->status . " \n ";
	    $carrier=PackageCarrier::where('trackingNumber', $post->trackinG_NO)->whereRaw("((status=1) or (ecoM_REGNUMBER is not null))")->first();
	    if($carrier) {
	        $num_cleaned++;
		echo "   ".$num_cleaned." ".$post->trackinG_NO."  ".$carrier->id." ".$carrier->ecoM_REGNUMBER." ".$carrier->insert_date_dec."\n";
		/*$carrier->ecoM_REGNUMBER=null;
		$carrier->status=0;
		$carrier->cost=0;
		$carrier->cost_usd=0;
		$carrier->currency=0;
		$carrier->insert_date_dec=NULL;
		$carrier->save();
		 */
		$str = "update package_carriers set ecoM_REGNUMBER=null,cost=0,status=0,cost_usd=0,currency=0,insert_date_dec=NULL where id=?";
                DB::update($str, [$carrier->id]);
		if($carrier->track_id) {
		    $track = Track::find($carrier->track_id);
		    if($track) {
	    		$track->status=5;
	    		$track->save();
	    		(new PackageService())->updateStatus($track, 5);
		        echo "       track status changed\n";
		    }
		}

	    }
	    continue;
        }
    }
}
