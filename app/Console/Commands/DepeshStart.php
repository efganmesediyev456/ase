<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Track;
use App\Models\PackageCarrier;
use App\Models\Package;
use App\Services\Package\PackageService;
use App\Models\CustomsModel;
use DB;

class DepeshStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'depesh:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Depesh start';

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
	$cm = new CustomsModel();
        $cm_ae = new CustomsModel(true);
        $cm->retryCount = 7;
        $cm->retrySleep = 0;
        $cm_ae->retryCount = 7;
        $cm_ae->retrySleep = 0;

	$ldate = date('Y-m-d H:i:s');
	$items = Track::select('tracks.*');
        $items = $items->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id');
        $items = $items->leftJoin('containers', 'tracks.container_id', 'containers.id');

	$items = $items->whereNull('package_carriers.deleted_at');
	$items = $items->whereNull('tracks.deleted_at');
	//$items = $items->whereNotIn('tracks.status',[14,16,17]);
	$items = $items->whereNull('containers.deleted_at');
	$items = $items->where('containers.depesh_start',1);
	$items = $items->whereNotNull('package_carriers.ecoM_REGNUMBER');
	$items = $items->whereNull('package_carriers.depesH_NUMBER');
	$items = $items->whereRaw("((DATEDIFF('" . $ldate . "',containers.depesh_start_at)<30))");
	//$items = $items->whereRaw("((tracks.depesh_start_at is NULL) or (TIME_TO_SEC(TIMEDIFF('" . $ldate . "',tracks.depesh_start_at))>=3*3600))");
	$items = $items->whereRaw("((tracks.depesh_start_at is NULL) or (TIME_TO_SEC(TIMEDIFF('" . $ldate . "',tracks.depesh_start_at))>=30*60))");
	$items = $items->orderBy('containers.depesh_start_at','asc');
	$items = $items->orderBy('tracks.depesh_start_at','asc');
	$items = $items->orderBy('tracks.created_at','asc');
	$items = $items->get();
	if(count($items) <=0 )
	    return;
	echo '        '.$ldate.' depesh start '.count($items)." tracks\n";
	$num=0;
	foreach($items as $item) {
	   $num++;
	   $track=Track::with(['carrier','container','airbox'])->find($item->id);
	   $carrier=$track->carrier;
	   if(!$carrier) {
		echo "   no smart\n";
	        continue;
	   }

	   $ldate = date('Y-m-d H:i:s');
  	   echo $num.' '.$ldate.' '.$track->container->name.' '.$carrier->trackingNumber."  ".$carrier->ecoM_REGNUMBER.' '.$carrier->depesH_NUMBER."\n";
	   if(!$track->container || !$track->container->depesh_start) {
		echo "   container not depesh started\n";
		continue;
	   }
	   if(!$carrier->ecoM_REGNUMBER) {
		echo "   no regNumber\n";
		continue;
	   }
	   if($carrier->depesH_NUMBER) {
		echo "   already depesh\n";
		continue;
	   }
	   $track->depesh_start_at = $ldate;
	   $track->bot_comment = 'Depesh start';
	   $track->save();

	   //approvesearch
           $cm_ae->regNumber = $carrier->ecoM_REGNUMBER;
           $res = $cm_ae->approvesearch();
           if (!isset($res->code)) {
                echo"   approvesearch Error: empty response \n";
                //continue;
	   } else if ($res->code != 200) {
	       $cm_ae->parse_error($res);
               echo "   approvesearch  errorMessage: " . $cm_ae->errorMessage." validationError: " . $cm_ae->validationError."\n";
               //continue;
	   }
	   //--------

	   //addtoboxes
	   $cm_ae->trackingNumber = $carrier->trackingNumber;
           $res = $cm_ae->addtoboxes();
           if (!isset($res->code)) {
                echo"   addtoboxes Error: empty response \n";
                //continue;
	   } else if ($res->code != 200) {
	       $cm_ae->isCommercial = false;
	       $cm_ae->trackingNumber = $carrier->trackingNumber;
	       $cm_ae->pinNumber = $carrier->fin;
	       $cpost = $cm_ae->get_carrierposts2();
	       if ($cpost->code == 200 && !empty($cpost->depesH_NUMBER) && $cpost->depesH_DATE ) {
           	   //if($track && !in_array($track->status,[14,16,17])) {
           	   if($track && in_array($track->status,[1,5,7])) {
               		$track->status=14;
               		$track->save();
               		(new PackageService())->updateStatus($track, 14);
               	   }
               	   $str = "update package_carriers";
                   $str .= " set depesH_NUMBER=?,depesH_DATE=?";
                   $str .= " where track_id=?";
                   DB::update($str, [
               		$cpost->depesH_NUMBER, $cpost->depesH_DATE 
              		,$track->id]);
                   echo"   already depesh \n";
                   continue;
	       }
               $cm_ae->parse_error($res);
               echo "   addtoboxes  errorMessage: " . $cm_ae->errorMessage." validationError: " . $cm_ae->validationError."\n";
               //continue;
	   }
	   //--------

	   //depesh
	   if($track->container && $track->container->name && $track->airbox && $track->airbox->name) {
	       $cm_ae->airWaybill = $track->container->name;
	       $cm_ae->depeshNumber = $track->airbox->name;
	   } else {
                echo"   depesh Error: no container or airbox \n";
                continue;
	   }
           $res = $cm_ae->depesh();
           if (!isset($res->code)) {
                echo"   depesh Error: empty response \n";
                continue;
	   } else if ($res->code != 200) {
               $cm_ae->parse_error($res);
               echo "   depesh  errorMessage: " . $cm_ae->errorMessage." validationError: " . $cm_ae->validationError."\n";
               continue;
           }
	   $ldate = date('Y-m-d H:i:s');
           //if($track && !in_array($track->status,[14,16,17])) {
           if($track && in_array($track->status,[1,5,7])) {
               $track->status=14;
               $track->save();
               (new PackageService())->updateStatus($track, 14);
           }
           $str = "update package_carriers";
           $str .= " set depesH_NUMBER=?,depesH_DATE=?";
           $str .= " where track_id=?";
           DB::update($str, [
               $cm_ae->depeshNumber, $ldate
              ,$track->id]);
	   //---------
	}
    }
}
