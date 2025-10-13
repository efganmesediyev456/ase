<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use DB;
use Illuminate\Console\Command;

class CarriersGoods extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:goods';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update carriers goods types';

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
        $res = $cm->updateGoods();
        echo $ldate . "  " . $res . "\n";
    }
}
