<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\Track;
use App\Models\CustomsModel;
use App\Services\Package\PackageService;

class CarriersTrackStatusUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers_track_status_update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update track customs status from customs';

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
	$cm = new CustomsModel();
	$cm->retryCount = 2;
        $cm->retrySleep = 0;
	$cm->isCommercial = false;
	$tracks = Track::whereNull('tracks.deleted_at')->where('tracks.status', 5)->whereIn('tracks.partner_id',[3,8,9]);
	$tracks = $tracks->whereNull('package_carriers.deleted_at');
	$tracks = $tracks->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id')->select('tracks.*', 'package_carriers.status as pc_status');
	$tracks = $tracks->get();
	$tracking_codes=[];
	$num=0;
	foreach($tracks as $track) {
	   if(in_array($track->tracking_code,$tracking_codes))
		continue;
	   $num++;
	   $tracking_codes[]=$track->tracking_code;
	   echo $num.' '.$track->tracking_code;
	   if($track->carrier && $track->carrier->status>0) {
	       $track->status=7;
	       $track->save();
	       (new PackageService())->updateStatus($track, 7);
	       echo " updated from DB\n";
	       continue;
	   }
	   $cm->trackingNumber = $track->tracking_code;
	   $cpost = $cm->get_carrierposts2();
	   if ($cpost->code == 200 && !empty($cpost->inserT_DATE) && $cpost->status>0) {
	       $cm->updateDB(NULL, $track->fin, $track->tracking_code, $ldate, $cpost);
	       $track->status=7;
	       $track->save();
	       (new PackageService())->updateStatus($track, 7);
	       echo " ".$cpost->inserT_DATE." ".$cpost->status." updated from Customs\n";
	   } else {
	       if($cpost->code != 200) {
		   echo " error ".$cpost->code;
	       } else {
		  echo " ".$cpost->inserT_DATE." ".$cpost->status;
	       }
	       echo " \n";
	   }
	   sleep(1);
	}

    }
}
