<?php

namespace App\Console\Commands;

use App\Models\AzerPoctModel;
use App\Models\Package;
use DB;
use Illuminate\Console\Command;

class AzerPoct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $azp = null;
    protected $signature = 'azerpoct {custom_id=0} {cmd=add}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Azerpoct synchronization';

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
        $sendTelegram = true;
        $this->azp = new AzerPoctModel();
        $custom_id = $this->argument('custom_id');
        $cmd = $this->argument('cmd');
        $ldate = date('Y-m-d H:i:s');
        $packages = [];
        //echo "custom_id=".$custom_id." cmd=".$cmd."\n";
        if ($custom_id) {
            if ($cmd == 'view')
                $packages = Package::select('packages.*')->leftJoin('users', 'users.id', '=', 'packages.user_id')->whereNull('packages.deleted_at')->whereRaw("(packages.created_at>='2022-04-20 00:00:00' or packages.updated_at>='2022-04-20 00:00:00')")->where('packages.custom_id', $custom_id)->get();
            else
                $packages = Package::select('packages.*')->leftJoin('users', 'users.id', '=', 'packages.user_id')->whereNull('packages.deleted_at')->whereRaw("(packages.created_at>='2022-04-20 00:00:00' or packages.updated_at>='2022-04-20 00:00:00')")->where('packages.azerpoct_send', 0)->where('packages.custom_id', $custom_id)->where('users.azerpoct_send', 1)->whereRaw("(azerpoct_at is null OR TIME_TO_SEC(TIMEDIFF('" . $ldate . "',azerpoct_at))>=3600)")->limit(1)->get();
        } else {
            $packages = Package::select('packages.*')->leftJoin('users', 'users.id', '=', 'packages.user_id')->whereNull('packages.deleted_at')->whereRaw("(packages.created_at>='2022-04-20 00:00:00' or packages.updated_at>='2022-04-20 00:00:00')")->where('packages.azerpoct_send', 0)->where('packages.status', 1)->where('users.azerpoct_send', 1)->whereRaw("(azerpoct_at is null OR TIME_TO_SEC(TIMEDIFF('" . $ldate . "',azerpoct_at))>=3600)")->limit(1)->get();
        }
        foreach ($packages as $package) {
            //continue;
            if ($cmd == 'view') {
                echo "Viewing order: " . $package->custom_id . "\n";
                $res = $this->azp->order_view($package);
                continue;
            }
            echo "Creating order: " . $package->custom_id . "\n";
            $res = $this->azp->order_add($package);
            if ($res == 'Ok') {
                $message = "✅ Ok package sent to AZER POCT\n";
                if (isset($package->user)) {
                    $message .= "<b>" . $package->user->name . ' ' . $package->user->surname . "</b>";
                    $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->user->customer_id . "'>" . $package->user->customer_id . "</a>)";
                }
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                $this->info($package->custom_id . " Ok");
                if ($sendTelegram) sendTGMessage($message);

            } else {
                $message = "🛑 Eror package sending to AZER POCT\n";
                if (isset($package->user)) {
                    $message .= "<b>" . $package->user->name . ' ' . $package->user->surname . "</b>";
                    $message .= "  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->user->customer_id . "'>" . $package->user->customer_id . "</a>)";
                }
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->custom_id . "</a>\n";
                $message .= 'Error: ' . $res . "\n";
                $this->info($package->custom_id . " error: " . $res);
                DB::update('update packages set azerpoct_at=? where id=?', [$ldate, $package->id]);
                if ($sendTelegram) sendTGMessage($message);
            }
        }

        if ($cmd == 'view' || $custom_id) {
            return;
        }

        $packages = Package::select('packages.*')->whereNull('packages.deleted_at')->where('packages.azerpoct_send', 1)->whereRaw('packages.paid<>packages.azerpoct_paid')->limit(2)->get();
        foreach ($packages as $package) {
            echo "Updating paid: " . $package->custom_id . "\n";
            $res = $this->azp->order_paid($package);
            if ($res) {
                $this->info($package->custom_id . " Ok");
            } else {
                $this->info($package->custom_id . " error: " . $res);
            }
        }


    }

}
