<?php

namespace App\Console\Commands;

use App\Models\Package;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:invoices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear invoices';

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
        $public_dir = public_path('/uploads/packages/');
        $files = scandir($public_dir);
        $invoices = [];
        $packages = Package::select(DB::raw("replace(invoice,'" . ENV('APP_URL') . "/public/uploads/packages/','') as invoice1,status,updated_at"))->whereNotNull('invoice')->whereRaw('(updated_at is null or TIMESTAMPDIFF(MONTH,updated_at,current_timestamp)<12 or TIMESTAMPDIFF(MONTH,created_at,current_timestamp)<24 )')->orderBy('invoice')->get();

        echo count($packages) . " packages \n";
        foreach ($packages as $package) {
            //echo "status: ".$package->status." updated_at: ".$package->updated_at."\n";
            $invoices[] = $this->rem_ext($package->invoice1);
        }

        $packages = Package::select(DB::raw("replace(screen_file,'" . ENV('APP_URL') . "/uploads/packages/','') as screen_file1"))->whereRaw('(updated_at is null or TIMESTAMPDIFF(MONTH,updated_at,current_timestamp)<6)')->whereNotNull('screen_file')->orderBy('screen_file')->get();

        foreach ($packages as $package) {
            $invoices[] = $this->rem_ext($package->screen_file1);
        }
        foreach ($files as $file) {
            if ($file == '.') continue;
            if ($file == '..') continue;
            if ($file == '.gitignore') continue;
            if (is_dir($file)) continue;
            if (in_array($this->rem_ext($file), $invoices)) {
                //echo " exists\n";
            } else {
           // echo $file;
           // echo "  " . $this->rem_ext($file);
           //     echo " not exists";
                echo " unlink ".$public_dir.$file." ". date('Y-m-d H:i:s',filemtime($public_dir.$file));
                if (unlink($public_dir . $file))
                    echo " deleted";
                echo "\n";
            }
        }
    }

    public function rem_ext($str)
    {
        return substr($str, 0, strpos($str, '.'));
    }
}
