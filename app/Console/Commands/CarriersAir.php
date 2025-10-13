<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use App\Models\Package;
use DB;
use Illuminate\Console\Command;

class CarriersAir extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:air {airWaybill} {depeshNumber}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List cairwaybillpackages';

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
        $airWaybill = $this->argument("airWaybill");
        $depeshNumber = $this->argument('depeshNumber');
	$cm=new CustomsModel();
	$cm->depeshNumber=$depeshNumber;
	$cm->airWaybill=$airWaybill;
        $ldate = date('Y-m-d H:i:s');
	$packages=$cm->air();
	dd($packages);
	$cnt=0;
	foreach($packages as $pkg) {
	    //print_r($package);
	    $cnt++;
	    echo sprintf("%03d", $cnt)." ".$pkg->trackinG_NO." ".$pkg->weighT_OF_GOODS." ".$pkg->airwaybill." ".$pkg->depesH_NUMBER." ".$pkg->depesH_DATE."\n";
	}
    }
}
