<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\UniTradeModel;
use App\Models\Package;

class UniTrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unitrade {parcel_id} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'UniTrade export';

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
	$parcel_id=$this->argument("parcel_id");
	$ut=new UniTradeModel();
	echo $ut->getParcelZip($parcel_id);

    }
}
