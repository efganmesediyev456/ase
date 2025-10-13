<?php

namespace App\Console\Commands;

use App\Models\Extra\Notification;
use App\Models\Package;
use Illuminate\Console\Command;

class Declaration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'declaration:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $packages = Package::whereNull('invoice')->whereNull('shipping_amount')->whereIn('status', [
            0,
       //     6,
        ])->whereNotNull('user_id')->whereNotNull('tracking_code')->where('id', '>=', 3000)->where('dec_message', '<', 1)->orderBy('id', 'asc')->get();

        foreach ($packages as $package) {
            $_package = Package::find($package->id);
            if ($_package->invoice && $_package->shipping_amount) {
                $this->info($package->id . ")" . $package->tracking_code . " skiped");
                continue;
            }
            $this->info($package->id . ")" . $package->tracking_code);
            Notification::sendPackage($package->id, 'no_declaration');
            $package->dec_message++;
            $package->save();
        }
    }
}
