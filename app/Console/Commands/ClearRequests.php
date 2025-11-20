<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearRequests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:requests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear requests';

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
	DB::delete("delete from requests where TIMESTAMPDIFF(DAY,created_at,current_timestamp)>80");
	DB::delete("delete from logs where TIMESTAMPDIFF(DAY,created_at,current_timestamp)>120");
	DB::delete("delete from notification_queues where TIMESTAMPDIFF(DAY,created_at,current_timestamp)>120");
    }

    public function rem_ext($str)
    {
        return substr($str, 0, strpos($str, '.'));
    }
}
