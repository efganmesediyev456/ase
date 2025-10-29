<?php

namespace App\Console\Commands;

use App\Models\Bag;
use App\Models\Extra\Notification;
use App\Models\Extra\SMS;
use App\Models\Package;
use App\Models\UkrExpressModel;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Console\Command;

class UkraineExpress2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $ue = null;
    protected $signature = 'ukraine:express2 {--type=none} {--cwb=} {--code=} {--arr=1} {--offset=0} {--limit=100} {--filter=} {--customer_id=} {--container_id=} {--airbox_id=} {--in_warehouse=}';
    protected $warehouse;
    protected $sendTelegram = true;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ukraine Express version 2';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->warehouse = Warehouse::find(11);
        $this->ue = new UkrExpressModel();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $option_type = $this->option('type');
        if ($option_type == "users") {
            $this->users_update();
            return;
        }
        if ($option_type == "packages-add") {
            $this->packages_add_to_ukr();
            return;
        }
        if ($option_type == "packages-update") {
            $this->packages_update_from_ukr();
            return;
        }
        if ($option_type == "packages-add-no-declared-price") {
            $this->packages_add_to_ukr_no_declared_price();
            return;
        }
        if ($option_type == "packages-add-old-notpacked") {
            $option_arr = $this->option('arr');
            $option_limit = $this->option('limit');
            $this->packages_add_to_ukr_old_notpacked($option_arr, $option_limit);
            return;
        }
        if ($option_type == "packages-pack") {
            $this->packages_update_packing_data();
            $this->packages_update_declaration();
            return;
        }
        if ($option_type == "packages-pack-reload") {
            $this->packages_update_packing_data(true);
            return;
        }
        if ($option_type == "packages-declaration") {
            $this->packages_update_declaration();
            return;
        }
        if ($option_type == "track-list") {
            $option_filter = $this->option('filter');
            $option_offset = $this->option('offset');
            $option_limit = $this->option('limit');
            $option_customer_id = $this->option('customer_id');
            $this->package_list($option_offset, $option_limit, $option_filter, $option_customer_id);
            return;
        }
        if ($option_type == "parcel-list") {
            $option_offset = $this->option('offset');
            $option_limit = $this->option('limit');
            $option_container_id = $this->option('container_id');
            $option_airbox_id = $this->option('airbox_id');
            $option_in_warehouse = $this->option('in_warehouse');
            $this->parcel_list($option_offset, $option_limit, $option_container_id, $option_airbox_id, $option_in_warehouse);
            return;
        }
        if ($option_type == "track-get") {
            $option_cwb = $this->option('cwb');
            $option_customer_id = $this->option('customer_id');
            if ($option_cwb) {
                $this->package_get($option_cwb, $option_customer_id);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "track-add") {
            $option_cwb = $this->option('cwb');
            if ($option_cwb) {
                $this->package_add_console($option_cwb);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "track-declaration") {
            $option_cwb = $this->option('cwb');
            if ($option_cwb) {
                $this->package_declaration_console($option_cwb);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "customer-add") {
            $option_cwb = $this->option('code');
            if ($option_cwb) {
                $this->user_add($option_cwb);
            } else {
                $this->error("No --code option");
            }
            return;
        }
        if ($option_type == "customer-get") {
            $option_cwb = $this->option('code');
            if ($option_cwb) {
                $this->user_get($option_cwb);
            } else {
                $this->error("No --code option");
            }
            return;
        }
        if ($option_type == "airbox-get") {
            $option_cwb = $this->option('code');
            if ($option_cwb) {
                $this->airbox_get($option_cwb);
            } else {
                $this->error("No --code option");
            }
            return;
        }
        if ($option_type == "track-get-declaration") {
            $option_cwb = $this->option('cwb');
            if ($option_cwb) {
                $this->package_get_declaration($option_cwb);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "track-delete") {
            $option_cwb = $this->option('cwb');
            if ($option_cwb) {
                $this->package_delete($option_cwb);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "track-get-photos") {
            $option_cwb = $this->option('cwb');
            if ($option_cwb) {
                $this->package_get_photos($option_cwb);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "track-pack-test") {
            $option_cwb = $this->option('cwb');
            if ($option_cwb) {
                $this->package_pack_test($option_cwb);
            } else {
                $this->error("No --cwb option");
            }
            return;
        }
        if ($option_type == "airbox-list") {
            $this->ue->airbox_list();
            return;
        }
        /*if($option_type=="track-list") {
        $this->ue->parcel_list();
        return;
            $tracks=$this->ue->track_list();
            if($tracks) {
                foreach($tracks as $track) {
                echo $track->id." customer id: ".$track->customer_id."  num: ".$track->number." weigh: ".($track->weight_in_grams/1000);
                echo " parcel(".$track->parcel_id.",".$track->parcel_code.")";
                echo "\n";
                if($track->parcel_id && $track->parcel_id>0) {
                    $u_parcel=$this->ue->parcel_get($track->customer_id,$track->parcel_id);
                    if($u_parcel) {
                        echo "   container id: ".$u_parcel->container_id;
                        echo "\n";
                    }
                }
                }
            }
            return;
        }*/
        if ($option_type == "test") {
            $this->test();
            return;
        }
        if ($option_type == "customer_checkers") {
            $this->customer_checkers();
            return;
        }
        echo "Unknown type\n";
        return;
        echo "TEST\n";
        //$this->users_update();
        //return;
        //$this->ue->parcel_list();
        //$this->ue->test_parcel_to_airbox(26705);
        //$this->ue->test_parcel_to_airbox(26723);
        //$this->ue->test_airbox_to_container(1);
        //$this->ue->airbox_list();
        //$u_airbox=$this->ue->airbox_get(1);
        //echo $u_airbox->code."\n";
        //return;
        //$package=Package::where('tracking_code','UTEST0000000011100US00101')->first();
        //$package=Package::where('tracking_code','test0000002')->first();
        //$package=Package::where('tracking_code','TEST00TEST0022')->first();
        //$package=Package::where('tracking_code','1ZTEST0000001')->first();
        //$this->ue->packing_data($package);
        //$this->ue->test_pack_tracking($package->ukr_express_id);
        //$track=$this->ue->track_get(1002850);
        //print_r($track);
        //return;
        //$this->ue->test_receive_tracking(1002852);
        //$this->ue->test_pack_tracking(1002852);
        //$this->ue->test_pack_tracking(1002850);
        //$this->ue->test_pack_tracking(1002955);
        //$this->ue->test_load_parcel(26704,'true');
        //$this->ue->test_receive_and_assign_tracking('TEST000AAAA0004',700080);
        //return;

        //$package=Package::find(47644);
        //$this->ue->test_receive_tracking(1003034);
        //$this->ue->test_pack_tracking(1003034);
        //$this->ue->test_parcel_to_airbox(26732);
        //$this->ue->test_airbox_to_container(10);
        //$this->ue->package_add($package);
        //$user = User::find(10063);
        //$ue->user_register($user);

        //$packages = Package::where('warehouse_id',11)->whereNull('ukr_express_id')->orderByDesc('id')->limit(10)->get();
        //foreach($packages as $package) {
        //    $this->ue->package_add($package);
        //}
        //$this->ue->airbox_list();
        //$tracks=$this->ue->track_list_parcel(26705);
        //$tracks=$this->ue->track_list_parcel(26704);
        //return;
        //$this->packages_update_packing_data();
        //$this->packages_sync_from_ukr();
        //$this->packages_add_to_ukr();

    }

    public function customer_checkers()
    {
        $res_ok = $this->ue->customers_all();

        //dd(($res_ok));
        $customer_codes = [];
        $customer_code_uk = [];
        $customer_code_data = [];

        foreach ($res_ok["data"] as $r) {
            $customer_codes[] = $r["code"];
            $customer_code_uk[$r["code"]] = $r["id"];
            $customer_code_data[$r["code"]] = $r;
        }

        $users = User::withTrashed()->whereIn("customer_id", $customer_codes)->whereNull("ukr_express_id")->get();

        if ($users->count() > 0) {
            foreach ($users as $user) {
                $this->info($user->customer_id);
                if (isset($customer_code_uk[$user->customer_id])) {
                    $this->info(" -> " . $customer_code_uk[$user->customer_id]);
                    //dd($customer_code_data[$user->customer_id]);
                    $user->ukr_express_id = $customer_code_uk[$user->customer_id];
                    $user->ukr_express_error_at = null;
                    $user->save();
                    $this->info($user->customer_id . " -> " . $user->ukr_express_id . " Deyisdirildi");
                }

            }
            $this->info("tapildi");
        } else {
            $this->info("Tapilmadi");

        }

        //dd($customer_codes);


        $this->info("Ddd");
    }

    public function err($module, $message)
    {
        $ldate = date('Y-m-d H:i:s');
        file_put_contents('/var/log/ase_uexpress2_error.log', $ldate . " " . $module . " " . $message . "\n", FILE_APPEND);
    }

    public function package_pack_test($cwb)
    {
        $package = Package::where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            return;
        }
        if (!$package->ukr_express_id) {
            $this->error("Package not in Ukr Express No ukr_express_id for cwb=" . $cwb);
            return;
        }
        //echo "Packing test track ukr_express_id: ".$package->ukr_express_id."\n";
        echo "Generate HTML: " . $package->generateHtmlInvoice(true, NULL, true) . "\n";
        //$track=$this->ue->packing_data($package,true);
        //print_r($track);
    }

    public function user_add($code)
    {
        $user = User::where('customer_id', $code)->first();
        if (!$user) {
            $this->error("No user with code=" . $code);
            return;
        }
        if ($user->ukr_express_id) {
            $this->line(" Updating customer " . $user->customer_id . " " . $user->full_name);
            //$this->error("User already in Ukr Express ukr_express_id: " . $user->ukr_express_id);
            //return;
        } else {
            $this->line(" Adding customer " . $user->customer_id . " " . $user->full_name);
        }
        $res_ok = $this->ue->user_register($user);
        if (!$res_ok) {
            $this->error('failed');
            $this->err("update_users", "   failed: " . $user->customer_id . " " . $user->full_name);
        } else
            $this->info('ok');
    }

    public function user_get($code)
    {
        $user = NULL;
        if (is_numeric($code))
            $user = User::withTrashed()->where('ukr_express_id', $code)->first();
        if (!$user)
            $user = User::where('customer_id', $code)->first();
        if (!$user) {
            $this->error("No user with code=" . $code);
            return;
        }
        if (!$user->ukr_express_id) {
            $this->error("User not in Ukr Express No ukr_express_id for code=" . $code);
            return;
        }
        echo "Getting customer by ukr_express_id: " . $user->ukr_express_id . "\n";
        $track = $this->ue->customer_get($user->ukr_express_id);
        print_r($track);
    }


    public function airbox_get($code)
    {
        $bag = Bag::where('custom_id', $code)->first();
        if (!$bag) {
            $this->error("No bag with code=" . $code);
            return;
        }
        if (!$bag->ukr_express_airbox_id) {
            $this->error("Bag not in Ukr Express No ukr_express_airbox_id for code=" . $code);
            return;
        }
        echo "Getting bag by ukr_express_airbox_id: " . $bag->ukr_express_airbox_id . "\n";
        $track = $this->ue->airbox_get($bag->ukr_express_airbox_id);
        print_r($track);
    }


    public function package_declaration_console($cwb)
    {
        $package = Package::where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            return;
        }
        echo "Declaration  track to ukr_express " . $package->tracking_code . "\n";
        $this->package_update_declaration($package, true);
    }

    public function package_add_console($cwb)
    {
        $package = Package::where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            return;
        }
        echo "Adding  track to ukr_express " . $package->tracking_code . "\n";
        $this->package_add($package, true);
    }


    public function package_get($cwb, $customer_id = NULL)
    {
        $package = NULL;
        if (is_numeric($cwb))
            $package = Package::withTrashed()->where('ukr_express_id', $cwb)->first();
        if (!$package)
            $package = Package::withTrashed()->where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::withTrashed()->where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            //return;
        }
        $ukr_express_id = $cwb;
        $ukr_express_customer_id = NULL;
        if ($package) {
            $ukr_express_id = $package->ukr_express_id;
            if ($customer_id) {
                $ukr_express_customer_id = $customer_id;
            } else if ($package->user) {
                $ukr_express_customer_id = $package->user->ukr_express_id;
            }
        } else {
            $this->error("Package not in Ukr Express No ukr_express_id for cwb=" . $cwb);
        }
        echo "Getting track by ukr_express_id=" . $ukr_express_id . " & customer_id=" . $ukr_express_customer_id . "\n";
        //echo "Package Invoice: ".$package->invoice."\n";
        $track = $this->ue->track_get($ukr_express_id, $ukr_express_customer_id);
        if (!$track) {
            echo "Getting track by number=" . $package->tracking_code . " & customer_id=" . $ukr_express_customer_id . "\n";
            $track = $this->ue->track_get_by_number($package->tracking_code, $ukr_express_customer_id);
        }
        echo "Track: \n";
        print_r($track);
        if (isset($track->customer_id)) {
            $parcel = $this->ue->parcel_get($track->customer_id, $track->parcel_id);
            echo "Parcel: \n";
            print_r($parcel);
        } else {
            echo "No customer\n";
        }
        if (isset($track->photos_info) && $track->photos_info->has_any_photos > 0) {
            $photos = $this->ue->track_get_photos($track->id, $track->customer_id);
            if (!$photos) $message = "Cannot get track photo " . $this->ue->message;
            echo "Photos: \n";
            print_r($photos);
        } else {
            echo "No photo\n";
        }
        if ($package) {
            // echo "ZPL: ".$this->ue->zpl($package)."\n";
        }
    }

    public function package_get_declaration($cwb)
    {
        $package = Package::where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            return;
        }
        if (!$package->ukr_express_id) {
            $this->error("Package not in Ukr Express No ukr_express_id for cwb=" . $cwb);
            return;
        }
        echo "Getting track by ukr_express_id: " . $package->ukr_express_id . " customer_id:" . $package->user->ukr_express_id . "\n";
        $track = $this->ue->track_get_declaration($package->ukr_express_id, $package->user->ukr_express_id);
        print_r($track);
    }

    public function package_delete($cwb)
    {
        $package = Package::withTrashed()->where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::withTrashed()->where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            return;
        }
        if (!$package->ukr_express_id) {
            $this->error("Package not in Ukr Express No ukr_express_id for cwb=" . $cwb);
            return;
        }
        echo "Deleting by ukr_express_id: " . $package->ukr_express_id . " customer_id:" . $package->user->ukr_express_id . "\n";
        $track = $this->ue->track_delete($package->ukr_express_id, $package->user->ukr_express_id);
        print_r($track);
    }

    public function package_get_photos($cwb)
    {
        $package = Package::where('tracking_code', $cwb)->first();
        if (!$package)
            $package = Package::where('custom_id', $cwb)->first();
        if (!$package) {
            $this->error("No package with cwb=" . $cwb);
            return;
        }
        if (!$package->ukr_express_id) {
            $this->error("Package not in Ukr Express No ukr_express_id for cwb=" . $cwb);
            return;
        }
        echo "Getting track's photos by ukr_express_id: " . $package->ukr_express_id . " customer_id:" . $package->user->ukr_express_id . "\n";
        $track = $this->ue->track_get_photos($package->ukr_express_id, $package->user->ukr_express_id);
        print_r($track);
    }

    public function users_update()
    {
        $ldate = date('Y-m-d H:i:s');
        $expUsers = env('UKR_EXPRESS_NEW_USERS');
        $usersSqlStr = '((parent_id in (' . $expUsers . ')) or (id in (' . $expUsers . ')))';
        $users = [];
        if ($expUsers)
            $users = User::whereRaw('(ukr_express_id is NULL or updated_at>ukr_express_update_at)')->whereRaw($usersSqlStr);
        else
            $users = User::whereRaw('(ukr_express_id is NULL or ukr_express_update_at is NULL or updated_at>ukr_express_update_at)');
        $users = $users->whereRaw("(ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',ukr_express_error_at))>3*3600)"); //3*3600
        $users = $users->limit(500)->get();
        $cnt = 1;
        if (count($users) > 0) {
            $this->line('Adding ' . count($users) . ' users');
            //$this->line('Users: '.$expUsers);
        }

        //dd($users->count());
        foreach ($users as $user) {
            $ldate = date('Y-m-d H:i:s');
            $this->line($ldate . " " . $cnt . " " . $user->customer_id . " " . $user->full_name);
            $cnt++;


            $res_ok = $this->ue->user_register($user);
            if (!$res_ok) {
                $user->ukr_express_error_at = $ldate;
                $user->save();
                $this->error('failed');
                $this->err("update_users", "   failed: " . $user->customer_id . " " . $user->full_name);
            } else
                $user->ukr_express_error_at = null;
            $user->save();
            $this->info('ok');
        }
    }

    public function test()
    {
        $package = Package::find(199383);
        //$this->ue->change_customer($package);
        //$res_ok=$this->ue->packing_data($package,true);
        //$content=$package->tracking_code ." (".$package->custom_id ."): ".$this->ue->message;
        $zpl = $this->ue->zpl($package);
        echo $zpl . "\n";
        //echo $package->generateHTMLInvoice()."\n";
    }

    public function packages_update_packing_data($reload = false)
    {
        $ldate = date('Y-m-d H:i:s');
        //$this->info("===== update packing data =====");
        $warehouse = $this->warehouse;

        $packages = Package::with(['parcel', 'bag', 'user']);
        $packages->whereRaw("(packages.ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',packages.ukr_express_error_at))>3*3600)");
        if ($warehouse->check_carriers) {
            $packages = $packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE')->whereRaw('((package_carriers.ecoM_REGNUMBER is not null) or (package_carriers.is_commercial=1))');
        }
        $packages = $packages->where(function ($q) use ($warehouse) {
            $q->orWhere('packages.warehouse_id', $warehouse->id)->orWhere('packages.country_id', $warehouse->country_id);
        })->whereNotNull('packages.ukr_express_id')->whereNull('packages.ukr_express_parcel_id')->whereRaw('(packages.ukr_express_pd is null or packages.ukr_express_pd < 2)')->whereIn('packages.status', [0, 1, 6])->limit(100)->get();
        //if(!$reload) {
        //    $packages=$packages->where('packages.ukr_express_pd', 0);
        //}
        //})->where('tracking_code','393798232483')->limit(1)->get();
        if (count($packages) > 0) {
            $this->info(count($packages) . " packages to update packing data");
        }
        $cnt = 1;
        foreach ($packages as $package) {
            $ldate = date('Y-m-d H:i:s');
            $this->line($ldate . " " . $cnt . " track: " . $package->tracking_code . " tracking_id: " . $package->ukr_express_id . " customer_id:" . $package->user->ukr_express_id);
            $this->line("  invoice: " . $package->generateHtmlInvoice());
            $cnt++;
            //if($reload)
            //continue;
            //continue;
            $res_ok = $this->ue->change_customer($package);
            if (!$res_ok) {
                $this->info("   warging: " . $this->ue->code . " " . $this->ue->message);
                $this->err("packing_data", "   warning: " . $package->tracking_code . " " . $this->ue->code . " " . $this->ue->message);
                continue;
            }
            $res_ok = $this->ue->package_additional_info($package);
            if (!$res_ok) {
                $this->err("additional_info", "   error");
            }
            $res_ok = $this->ue->packing_data($package);
            //continue;
            if ($res_ok) {
                $package->ukr_express_pd = $package->ukr_express_pd + 1;
                $package->ukr_express_status = 9;
                $package->ukr_express_error_at = null;
                $package->save();
            } else {
                if ($this->ue->code == 'tracking_number_not_found') {
                    $res_ok = $this->package_add($package);
                    if ($res_ok)
                        $res_ok = $this->ue->packing_data($package);
                    if ($res_ok) {
                        $package->ukr_express_pd = 1;
                        $package->ukr_express_error_at = null;
                        $package->save();
                    }
                }
            }
            if (!$res_ok) {
                $message = "🛑 Eror packing data to Ukraine Express\n";
                if ($package->user)
                    $message .= " <b>" . $package->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->user->customer_id . "'>" . $package->user->customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->tracking_code . "(" . $package->custom_id . ")</a>\n";
                $message .= "Error: " . $this->ue->message . "\n";
                $content = "Error packing data to Ukr Express " . $package->tracking_code . " (" . $package->custom_id . "): " . $this->ue->message;
                $this->info("   failed: " . $this->ue->code . " " . $this->ue->message);
                $this->err("packing_data", "   failed: " . $package->tracking_code . " " . $this->ue->code . " " . $this->ue->message);
                $package->bot_comment = "pack error " . $this->ue->code . " " . $this->ue->message;
                $package->ukr_express_error_at = $ldate;
                $package->save();
                if ($this->sendTelegram) sendTGMessage($message);
            } else {
                $this->info("   Ok res: " . $this->ue->code . "  message: " . $this->ue->message);
            }
        }
    }

    public function packages_update_declaration()
    {
        $ldate = date('Y-m-d H:i:s');
        //$this->info("===== update declaration data =====");
        $warehouse = $this->warehouse;

        $packages = Package::with(['parcel', 'bag', 'user']);
//        $packages = Package::where('custom_id','ASE0960854047355')->get();
        $packages->whereRaw("(packages.ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',packages.ukr_express_error_at))>=3*3600)");
        $packages = $packages->where(function ($q) use ($warehouse) {
            $q->orWhere('packages.warehouse_id', $warehouse->id)->orWhere('packages.country_id', $warehouse->country_id);
        })->whereNotNull('packages.ukr_express_id')->whereNull('packages.ukr_express_parcel_id')->whereRaw('packages.ukr_express_pd > 0')->whereRaw('(packages.ukr_express_dec is null or packages.ukr_express_dec < 2)')->whereIn('packages.status', [0, 1, 6])->limit(100)->get();
        //})->where('tracking_code','393798232483')->limit(1)->get();
//        $packages = Package::with(['parcel', 'bag', 'user'])->where('custom_id', 'ASE4647724922721')->get();
        if (count($packages) > 0) {
            $this->info(count($packages) . " packages to update declaration data");
        }
        $cnt = 1;
        foreach ($packages as $package) {
            $ldate = date('Y-m-d H:i:s');
            $this->line($ldate . " " . $cnt . " " . $package->tracking_code . " " . $package->ukr_express_id . " " . $package->detailed_type);
            $cnt++;
            //continue;
            $this->package_update_declaration($package);
        }
    }

    public function package_update_declaration($package, $fromConsole = false)
    {
        $ldate = date('Y-m-d H:i:s');
        $res_ok = $this->ue->declaration($package);
        if ($res_ok) {
            $package->ukr_express_dec = $package->ukr_express_dec + 1;
            $package->ukr_express_error_at = null;
            $package->ukr_express_status = 10;
            $package->bot_comment = "declaration Ok";
            $package->save();
        }
        if (!$res_ok) {
            $message = "🛑 Eror declaration data to Ukraine Express\n";
            if ($package->user)
                $message .= " <b>" . $package->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->user->customer_id . "'>" . $package->user->customer_id . "</a>)";
            $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->tracking_code . "(" . $package->custom_id . ")</a>\n";
            $message .= "Error: " . $this->ue->message . "\n";
            $content = "Error declaration data to Ukr Express " . $package->tracking_code . " (" . $package->custom_id . "): " . $this->ue->message;
            $this->info("   failed: " . $this->ue->code . " " . $this->ue->message);
            if (!$fromConsole) {
                $this->err("update_declaration", "   failed: " . $package->tracking_code . " " . $this->ue->code . " " . $this->ue->message);
                $package->bot_comment = "declaration error " . $this->ue->code . " " . $this->ue->message;
                $package->ukr_express_error_at = $ldate;
                $package->save();
                if ($this->sendTelegram) sendTGMessage($message);
            }
        } else {
            $this->info("   Ok res: " . $this->ue->code . "  message: " . $this->ue->message);
        }
    }

    public function package_update($package)
    {
        $ldate = date('Y-m-d H:i:s');
        $tracking_id = $package->ukr_express_id;
        $customer_id = NULL;
        if ($package->user)
            $customer_id = $package->user->ukr_express_id;
        $track = null;
        if (!$tracking_id) {
            if (!$this->package_add($package)) {
                $this->info("  Failed add package " . $package->tracking_code);
                return false;
            }
            $tracking_id = $package->ukr_express_id;
            sleep(2);
            $track = $this->ue->track_get($tracking_id, $customer_id);
        } else {
            $track = $this->ue->track_get($tracking_id, $customer_id);
        }
        $new_weight = NULL;
        $new_length = NULL;
        $new_width = NULL;
        $new_height = NULL;
        $new_fee = NULL;
        if (!$track) {
            sleep(2);
            $track = $this->ue->track_get($tracking_id, $customer_id);
        }
        if (!$track) {
            if ($this->package_add($package)) {
                $tracking_id = $package->ukr_express_id;
                sleep(2);
                $track = $this->ue->track_get($tracking_id, $customer_id);
            }
        }
        if (!$track) {
            $this->info("  Cannot get package " . $package->tracking_code . " (" . $tracking_id . ") from Ukraine Express!");
            $this->err("package_update", "  Cannot get package " . $package->tracking_code . " (" . $tracking_id . ") from Ukraine Express!");
            $message = "🛑 Eror add/update from Ukraine Express\n";
            $message .= "Error: " . "  Cannot get package " . $package->tracking_code . " (" . $tracking_id . ") from Ukraine Express!";
            if ($this->sendTelegram) sendTGMessage($message);
            $package->bot_comment = "update  error get package " . $this->ue->code . " " . $this->ue->message;
            $package->ukr_express_error_at = $ldate;
            $package->save();
            return false;
        }
        if ($track->weight_in_grams)
            $new_weight = number_format(0 + round($track->weight_in_grams / 1000, 2), 2, ".", "");
        if (isset($track->dimensions)) {
            if (isset($track->dimensions->length_mm) && $track->dimensions->length_mm) {
                $new_length = number_format(0 + round($track->dimensions->length_mm / 10, 2), 2, ".", "");
            }
            if (isset($track->dimensions->width_mm) && $track->dimensions->width_mm) {
                $new_width = number_format(0 + round($track->dimensions->width_mm / 10, 2), 2, ".", "");
            }
            if (isset($track->dimensions->height_mm) && $track->dimensions->height_mm) {
                $new_height = number_format(0 + round($track->dimensions->height_mm / 10, 2), 2, ".", "");
            }
        }

        $parcel = NULL;
        if (isset($track->customer_id) && isset($track->parcel_id) && $track->customer_id && $track->parcel_id) {
            $parcel = $this->ue->parcel_get($track->customer_id, $track->parcel_id);
        }
        if ($parcel) {
            if ($parcel->weight_in_grams)
                $new_weight = number_format(0 + round($parcel->weight_in_grams / 1000, 2), 2, ".", "");
            if (isset($parcel->dimensions)) {
                if (isset($parcel->dimensions->length_mm) && $parcel->dimensions->length_mm) {
                    $new_length = number_format(0 + round($parcel->dimensions->length_mm / 10, 2), 2, ".", "");
                }
                if (isset($parcel->dimensions->width_mm) && $parcel->dimensions->width_mm) {
                    $new_width = number_format(0 + round($parcel->dimensions->width_mm / 10, 2), 2, ".", "");
                }
                if (isset($parcel->dimensions->height_mm) && $parcel->dimensions->height_mm) {
                    $new_height = number_format(0 + round($parcel->dimensions->height_mm / 10, 2), 2, ".", "");
                }
            }
        }
        if (isset($track->fees) && isset($track->fees->receiving) && ($track->fees->receiving))
            $new_fee = number_format(0 + round($track->fees->receiving, 2), 2, ".", "");
        $user = null;
        $new_package = false;
        $user = $package->user;
        if ($track->customer_id && $user->ukr_express_id != $track->customer_id) {
            $this->info("  diff customers " . $user->ukr_express_id . "  " . $track->customer_id);
            if (!$this->ue->change_customer($package, $track->customer_id)) {
                $this->info("  Cannot change customer " . $track->number . " (" . $tracking_id . "," . $track->customer_id . ") to " . $user->ukr_express_id);
                $this->err("package_update", "  Cannot change customer " . $track->number . " (" . $tracking_id . "," . $track->customer_id . ") to " . $user->ukr_express_id);
                $message = "🛑 Eror add/update from Ukraine Express\n";
                if ($package->user)
                    $message .= " <b>" . $user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $user->customer_id . "'>" . $user->customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->tracking_code . "</a>\n";
                $message .= "Error: " . "  Cannot change customer " . $track->number . " (" . $tracking_id . "," . $track->customer_id . ") to " . $user->ukr_express_id . "\n";
                $package->bot_comment = "change customer error " . $this->ue->code . " " . $this->ue->message;
                $package->ukr_express_status = 102;
                $package->ukr_express_error_at = $ldate;
                $package->save();
                sendTGMessage($message);
                return false;
                //} else {
                //      $customer_id=$user->ukr_express_id;
            }
        }
        if ($new_weight && $package->getWeight() != $new_weight) {
            $message = "✅ Package updated from Ukraine Express";
            if ($user)
                $message .= " <b>" . $user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $user->customer_id . "'>" . $user->customer_id . "</a>)";
            $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $track->number . "'>" . $track->number . "</a>\n";
            $message .= " updated weight from: " . $package->getWeight() . " to " . $new_weight;
            if ($package->user_id && !$package->show_label) {
                $package->show_label = true;
            }

            if ($package->status == 6) {
                // Send Notification
                $package->status = 0;
                Notification::sendPackage($package->id, '0');
                /* Send notification */
                $message .= " notification sent to user " . ($package->user ? $package->user->full_name : 'NotFound');
                sendTGMessage($message);
            }
            if ($new_weight) {
                $package->weight = $new_weight;
                $package->weight_goods = $new_weight;
                $this->info("  updated package " . $track->number . " (" . $track->id . "," . $track->customer_id . ") weight from " . $package->getWeight() . " to " . $new_weight);
            }
            if ($new_width)
                $package->width = $new_width;
            if ($new_length)
                $package->length = $new_length;
            if ($new_height)
                $package->height = $new_height;
            if ($new_fee)
                $package->additional_delivery_price = $new_fee;
            //$package->tracking_code = $track->number;
            $package->bot_comment = 'Updated';
            $package->ukr_express_error_at = null;
            $package->ukr_express_status = 8;
            //$package->ukr_express_status = $status + 2;
            $package->save();
            return true;
        } else {
            $package->bot_comment = 'Update';
            $package->ukr_express_error_at = null;
            $package->ukr_express_status = 8;
            $package->save();
        }
        return false;
    }

    public function package_add($package, $fromConsole = false)
    {
        $message = '';
        $content = '';
        $ldate = date('Y-m-d H:i:s');
        $res_ok = $this->ue->package_add($package, $fromConsole);
        if ($res_ok) {
            $message = "✅ Package added to  Ukraine Express\n";
            if ($this->ue->trackingNumber) {
                $package->u_tracing_code = $package->tracking_code;
                $package->tracking_code = $this->ue->trackingNumber;
            }
            if ($package->user)
                $message .= " <b>" . $package->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->user->customer_id . "'>" . $package->user->customer_id . "</a>)";
            $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->tracking_code . "</a>\n";
            if ($this->ue->trackingNumber) {
                $package->u_tracing_code = $package->tracking_code;
                $package->tracking_code = $this->ue->trackingNumber;
            }
            $package->ukr_express_error_at = null;
            $package->ukr_express_id = $this->ue->id;
            $package->ukr_express_status = 1;
            $package->bot_comment = 'Added';
            $this->info("   ok");
        } else {
            $message = "🛑 Eror adding package to Ukraine Express\n";
            if ($package->user)
                $message .= " <b>" . $package->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $package->user->customer_id . "'>" . $package->user->customer_id . "</a>)";
            $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $package->custom_id . "'>" . $package->tracking_code . "</a>\n";
            $message .= "Error: " . $this->ue->message . "\n";
            $content = "Error adding to Ukr Express " . $package->tracking_code . "(" . $package->custom_id . "): " . $this->ue->message;
            $this->info("   failed: " . $this->ue->code . " " . $this->ue->message);
            if (!$fromConsole) {
                $this->err("package_add", "   failed: " . $package->tracking_code . " " . $this->ue->code . " " . $this->ue->message);
                if (!$package->ukr_express_error_at)
                    SMS::sendPureTextByNumber(env('UKRAINE_ERROR_PHONE'), $content);
                $package->ukr_express_status = 104;
                $package->bot_comment = "Add error " . $this->ue->message;
                $package->ukr_express_error_at = $ldate;
            }
        }
        if (!$fromConsole) {
            if ($this->sendTelegram) sendTGMessage($message);
        }
        $package->save();
        return $res_ok;
    }

    public function packages_add_to_ukr_old_notpacked($option_arr, $option_limit)
    {
        $ldate = date('Y-m-d H:i:s');
        //$this->info("===== add new packages =====");
        //$this->line("");
        $warehouse = $this->warehouse;
        $arr1 = ['ASE9160717449204', 'ASE7501589102986', 'ASE2067259885153', 'ASE0068372585862', 'ASE7330424076252', 'ASE7174529350226', 'ASE0873119729483', 'ASE5966147688679', 'ASE3774252885976', 'ASE8147716905448', 'ASE2452914769282', 'ASE8958560463368', 'ASE1049990642366', 'ASE1416669964265', 'ASE0568734256697', 'ASE3070774336405', 'ASE3249450220996', 'ASE7178283467101', 'ASE4626129469351', 'ASE5991280566714', 'ASE5594963104344', 'ASE2264064774848', 'ASE8803256655240', 'ASE7097098225211', 'ASE9009865095532', 'ASE6665055518450', 'ASE8712849746613', 'ASE6940544815017', 'ASE5996000542601', 'ASE5548571491299', 'ASE5350786062424', 'ASE3707606143746', 'ASE5756414047452', 'ASE8420460512152', 'ASE1304766507699', 'ASE1602301018430', 'ASE9018593178233', 'ASE7084945735554', 'ASE5280422323579', 'ASE7694939080360', 'ASE9100183109225', 'ASE6971185365603', 'ASE4327412460185', 'ASE9102886691926', 'ASE2074198133957', 'ASE9998157132061', 'ASE0753637352532', 'ASE7594262968068', 'ASE7751905589025', 'ASE0634070522010', 'ASE2210296265553', 'ASE2005401515385', 'ASE4716092186137', 'ASE4205901456735', 'ASE0862501280079', 'ASE5529406340831', 'ASE0827165011282', 'ASE4622047909409', 'ASE4159538932036', 'ASE5893120232259', 'ASE4537619737960', 'ASE3456479154151', 'ASE6902936071648', 'ASE2161081416499', 'ASE5823260924439', 'ASE8736820677500', 'ASE6106887532549', 'ASE9378385492976', 'ASE8576196391483', 'ASE5687354350334', 'ASE6765075941858', 'ASE8476366372121', 'ASE0251445463284', 'ASE8885112205400', 'ASE7192038915936', 'ASE6677060920357', 'ASE0257517847154', 'ASE0499976356792', 'ASE1864995461595', 'ASE8741807721306', 'ASE4812018739023', 'ASE5751916510217', 'ASE9060785892478', 'ASE4905322834134', 'ASE8160970519609', 'ASE6259017838229', 'ASE5302754836199', 'ASE7619860272773', 'ASE8326586307571', 'ASE9220348437795', 'ASE5290040409405', 'ASE8089111866232', 'ASE3653493568014', 'ASE3320165592769', 'ASE8823087676296', 'ASE7433945142640', 'ASE0885669264893', 'ASE5563975303527', 'ASE9232181503669', 'ASE7934963135331', 'ASE6450031830438', 'ASE3975914858583', 'ASE8276203956032', 'ASE9754087425519', 'ASE4419597542662', 'ASE1086837167270', 'ASE1218393654455', 'ASE8828423350485', 'ASE4221484205308', 'ASE7236403485351', 'ASE3562452793967', 'ASE8420835239599', 'ASE4103654525032', 'ASE0768364927172', 'ASE4545503719292', 'ASE9029618757694', 'ASE4499194830029', 'ASE5558107808124', 'ASE2920035111705', 'ASE4064007775461', 'ASE2178509436001', 'ASE9890741784342', 'ASE9225332945586', 'ASE1055081991708', 'ASE5779432408234', 'ASE5796503517360', 'ASE3050214306186', 'ASE4817325669876', 'ASE2247456174064', 'ASE7460735969780', 'ASE4788631452741', 'ASE6494277776260', 'ASE1638758554857', 'ASE1262192076284', 'ASE0786548785059', 'ASE7552016239072', 'ASE4003270006464', 'ASE0715058922050', 'ASE0291140771030', 'ASE9823329188340', 'ASE5504934301782', 'ASE1909220736098', 'ASE1802470136502', 'ASE0960106648816', 'ASE0463333834182', 'ASE1728937083479', 'ASE5726082996135', 'ASE7640310046666', 'ASE9215217819832', 'ASE7257717649500'];
        $arr2 = ['ASE3953474358489', 'ASE8359973406820', 'ASE1804962897935', 'ASE6256444681856', 'ASE8440463806131', 'ASE0433260161132', 'ASE1397252603149', 'ASE8759775978659', 'ASE5214734813449', 'ASE7346324243932', 'ASE5282359446098', 'ASE4702970686705', 'ASE0530329763587', 'ASE9921939930356', 'ASE2959906901436', 'ASE6701912155687', 'ASE3011655388178', 'ASE7452537614667', 'ASE7256045003049', 'ASE4063454068152', 'ASE9777634529817', 'ASE1536456132277', 'ASE5409543142583', 'ASE3349162947864', 'ASE7498358807742', 'ASE3330473738279', 'ASE3791861572967', 'ASE4237352322562', 'ASE6253455357448', 'ASE6705793259539', 'ASE5473708313136', 'ASE2412673169931', 'ASE3895121149396', 'ASE1982579937041', 'ASE7184679139224', 'ASE6940287354686', 'ASE6649021402955', 'ASE4039148888295', 'ASE2393076388251', 'ASE2483049343324', 'ASE6589342799899', 'ASE2405934021711', 'ASE1673729618394', 'ASE5952770090601', 'ASE0580427040645', 'ASE1327962436952', 'ASE1196974616527', 'ASE4541536694745', 'ASE0817627743830', 'ASE3729396838896', 'ASE0234816562543', 'ASE1076344688025', 'ASE8669746765017', 'ASE5893191281002', 'ASE6285715318973', 'ASE4424024854556', 'ASE2668114288523', 'ASE1710957002696', 'ASE6449014728110', 'ASE8036629864692', 'ASE8042108723840', 'ASE0204878820179', 'ASE3723103085217', 'ASE8021211095329', 'ASE3890225866739', 'ASE7686984253011', 'ASE7474188427213', 'ASE1470075805291', 'ASE8820392747195', 'ASE2732569365313', 'ASE8185123395507', 'ASE9553055793654', 'ASE4817246410332', 'ASE5186022073209', 'ASE5895216039865', 'ASE4568617976233', 'ASE7953175989652', 'ASE7690780984009', 'ASE1037566402120', 'ASE2399869597637', 'ASE7569347768560', 'ASE5556244456962', 'ASE5172657481687', 'ASE7320728704085', 'ASE8005635726436', 'ASE8578932277181', 'ASE7489056851525', 'ASE3132767681834', 'ASE1698710155297', 'ASE2428732663899', 'ASE1492811477634', 'ASE2665270468883', 'ASE6199073962989', 'ASE1809286776686', 'ASE2794137608651', 'ASE7473244028155', 'ASE5034251510054', 'ASE2357759350271', 'ASE6374819006904', 'ASE3548175692713', 'ASE6713666586879', 'ASE7696139622057', 'ASE4416870920372', 'ASE4155401156732', 'ASE3238194268339', 'ASE1864574312971', 'ASE7176353738554', 'ASE2936378742487', 'ASE2588357791709', 'ASE1663985645849', 'ASE5384749643060', 'ASE2555377893081', 'ASE3311430497420', 'ASE0037566416651', 'ASE0559708724547', 'ASE2440080482994', 'ASE3174942696885', 'ASE8326827730548', 'ASE4113138950380', 'ASE1702244249130', 'ASE9280431044156', 'ASE2274092099087', 'ASE8972346212275', 'ASE4259576658949', 'ASE2408461339910', 'ASE0796204864924', 'ASE8816771714175', 'ASE6580642169865', 'ASE3920406682543', 'ASE1565980473282', 'ASE5445491067674', 'ASE9583715762303', 'ASE7272175142668', 'ASE9967589797790', 'ASE8245336877356', 'ASE2811963667385', 'ASE7772647477559', 'ASE3841046705275', 'ASE8048152600124', 'ASE2770043042722', 'ASE1404311047409', 'ASE7842350898797', 'ASE3469560770094', 'ASE0537314749097', 'ASE5408804695955', 'ASE2306281872491', 'ASE2378837235863', 'ASE6191709553627', 'ASE0343847324582', 'ASE2991098516823'];
        $arr3 = ['ASE6325017034695', 'ASE1788781078935', 'ASE7028749098526', 'ASE2467389654421', 'ASE0143820081374', 'ASE9936198593794', 'ASE1749177779806', 'ASE5447808105380', 'ASE7416170845954', 'ASE2474389023492', 'ASE6558104024172', 'ASE8853703965725', 'ASE7267427002630', 'ASE4128355205102', 'ASE0726650088739', 'ASE0481562059835', 'ASE0764444786236', 'ASE2506191694778', 'ASE7432074908047', 'ASE1607424494488', 'ASE1800539581676', 'ASE1856756697262', 'ASE5434768914456', 'ASE6811873176088', 'ASE4251757949587', 'ASE9637689712023', 'ASE2600698252315', 'ASE5588624591680', 'ASE5629633865744', 'ASE4997739806928', 'ASE3249167798917', 'ASE2265506254816', 'ASE3079505974190', 'ASE1841620965638', 'ASE8343019794516', 'ASE4367035738906', 'ASE0384654098941', 'ASE3557979924293', 'ASE2135997431715', 'ASE2658451752045', 'ASE8782947107328', 'ASE2599253660810', 'ASE4431169931472', 'ASE9832584792609', 'ASE7575540726652', 'ASE5081846061757', 'ASE5351563109232', 'ASE9855615110219', 'ASE8217188625325', 'ASE3024243979509', 'ASE6273973631652', 'ASE7163856320381', 'ASE7374919542847', 'ASE3225899788626', 'ASE7074124739581', 'ASE8867812091527', 'ASE4942673353754', 'ASE3642452860835', 'ASE6223810695684', 'ASE4973446749877', 'ASE6972368802378', 'ASE0571856737533', 'ASE9084873707750', 'ASE5814508908909', 'ASE2454322632996', 'ASE4442730715044', 'ASE6432336094302', 'ASE8206579881379', 'ASE4390808094989', 'ASE0292806101414', 'ASE8216413013955', 'ASE3565748241717', 'ASE4187433180279', 'ASE6393025357995', 'ASE5981037889711', 'ASE6080195637787', 'ASE0573775817499', 'ASE7246319299257', 'ASE5235124611136', 'ASE7675030129225', 'ASE8771872944492', 'ASE5463685291768', 'ASE6403641114994', 'ASE3481544139693', 'ASE3718109982525', 'ASE2134047987721', 'ASE6920196939780', 'ASE5338475966550', 'ASE1725414582451', 'ASE6997774508882', 'ASE4177809366308', 'ASE4351787995640', 'ASE5203570419291', 'ASE6410398400453', 'ASE9996985764131', 'ASE3037430803700', 'ASE5489034641859', 'ASE0884945716794', 'ASE0764620611688', 'ASE1739118794394', 'ASE5451314562633', 'ASE9345485924845', 'ASE7779521258032', 'ASE4486548829816', 'ASE2250872905118', 'ASE2658343535657', 'ASE6952454968737', 'ASE6309900014539', 'ASE5462601126095', 'ASE7268497301842', 'ASE5080730056116', 'ASE2937072188658', 'ASE8719632574929', 'ASE3894981260592', 'ASE3875668851863', 'ASE3371520368546', 'ASE0357234787999', 'ASE9849467188600', 'ASE1349719336086', 'ASE4207714637859', 'ASE8733730582666', 'ASE1350632001029', 'ASE3646654270976', 'ASE0409733246707', 'ASE8915734080834', 'ASE6531604235496', 'ASE1105898125853', 'ASE3261061929492', 'ASE3974766714540', 'ASE3759161265424', 'ASE3003738841959', 'ASE6292931172671', 'ASE8938112121418', 'ASE3967532320426', 'ASE5549212152383', 'ASE6866519109179', 'ASE3778227544877', 'ASE3778227544877', 'ASE4343485456572', 'ASE5215356236832', 'ASE8163179951127', 'ASE5139276360912', 'ASE3476044742574', 'ASE7348763796062', 'ASE1388828039448', 'ASE0081639242724', 'ASE4112669686488', 'ASE7793943237206', 'ASE3398353202335', 'ASE1253010762155'];
        $arr4 = ['ASE1176398408147', 'ASE8357205347653', 'ASE0213730656544', 'ASE8638883367304', 'ASE3306743181133', 'ASE5731346102085', 'ASE7105723892349', 'ASE6859875689765', 'ASE7095097880388', 'ASE8080507582493', 'ASE1934158242157', 'ASE1701863780686', 'ASE5300557598432', 'ASE8521125183073', 'ASE8473383737042', 'ASE5103729507317', 'ASE4583708729401', 'ASE4386107810584', 'ASE7431342537521', 'ASE1520699674741', 'ASE5426853813705', 'ASE6644808702381', 'ASE5970583600130', 'ASE1760426142541', 'ASE7769019874955', 'ASE5556846671237', 'ASE2048909783870', 'ASE1822116400306', 'ASE8613285552734', 'ASE8181916933998', 'ASE4104278278359', 'ASE9874480363677', 'ASE9303673119160', 'ASE7989894955224', 'ASE4798379568757', 'ASE9040773079480', 'ASE0965628469240', 'ASE7010072654658', 'ASE2950162921134', 'ASE2922674719675', 'ASE7794766938713', 'ASE8990446614729', 'ASE2128487312238', 'ASE5165437352934', 'ASE8340367672014', 'ASE7899099694454', 'ASE2341846954588', 'ASE8445551489721', 'ASE4844560325046', 'ASE4834585959499', 'ASE7792028442236', 'ASE6869701945850', 'ASE2087713397246', 'ASE9687284135552', 'ASE9374115577415', 'ASE7171502269706', 'ASE3046093030064', 'ASE7855338971304', 'ASE6784163677897', 'ASE3970147028061', 'ASE7814332749121', 'ASE6863436259317', 'ASE6986431121926', 'ASE9801962457157', 'ASE2329573087261', 'ASE1832701692691', 'ASE5803375076851', 'ASE3851622166638', 'ASE8980824859546', 'ASE1770838142123', 'ASE2951962839560', 'ASE4924476684841', 'ASE8171421075125', 'ASE4013820152040', 'ASE3948640259962', 'ASE1902469562973', 'ASE0982573820327', 'ASE3618826136708', 'ASE5012866242567', 'ASE6474141455897', 'ASE2279264194288', 'ASE6517106147268', 'ASE3665221937902', 'ASE7342205598629', 'ASE3237264948033', 'ASE0575714624564', 'ASE1980687660279', 'ASE4875508639138', 'ASE2630852104152', 'ASE5763628481179', 'ASE3990325950075', 'ASE5042540705934', 'ASE9032983839373', 'ASE9021712739132', 'ASE7369340059387', 'ASE1268112268499', 'ASE6215354491165', 'ASE7859118723312', 'ASE4129924876026', 'ASE6260504591009', 'ASE1788906350658', 'ASE1841654314764', 'ASE1305853156609', 'ASE0252769812614', 'ASE8556462353520', 'ASE8419110201667', 'ASE2424006071202', 'ASE2509478714620', 'ASE7501517058205', 'ASE3091371387987', 'ASE1406023130613', 'ASE8187260586172', 'ASE9137644936663', 'ASE4368289242680', 'ASE2487364031740', 'ASE2889820723981', 'ASE1659546969793', 'ASE1097040552571', 'ASE0577913384404', 'ASE7150640781111', 'ASE5985396014647', 'ASE0746922801738', 'ASE5797760369643', 'ASE2099021768250', 'ASE9903377102654', 'ASE7491195673320', 'ASE7889289255484', 'ASE7349828649255', 'ASE7486967624654', 'ASE2441744958977', 'ASE9596742198755', 'ASE6467066055776', 'ASE7166898069495', 'ASE1724389009807', 'ASE0771656852840', 'ASE0519539255556', 'ASE1183768218113', 'ASE6436870586301', 'ASE5430598016809', 'ASE4638805091971', 'ASE0599127955332', 'ASE6406437010362', 'ASE8961451579615', 'ASE9911225287338', 'ASE9002293312359', 'ASE1082086551831', 'ASE7077101734766', 'ASE5936206024105', 'ASE7621012263011', 'ASE4863041553969'];
        $arr5 = ['ASE3002754603243', 'ASE7639244575304', 'ASE5091340342179', 'ASE9962885009800'];
        $arr6 = ['ASE9360337111920', 'ASE8497870473117', 'ASE5951284862678', 'ASE4816357293123', 'ASE7974302454660', 'ASE3248519460414', 'ASE3376421667631', 'ASE2578270679787', 'ASE8786810954998', 'ASE7278648230760', 'ASE6677714981727', 'ASE7932099214695', 'ASE1014726758857', 'ASE9536429858832', 'ASE5640783934533', 'ASE8434853346692', 'ASE5963642633661', 'ASE5766900803515', 'ASE6405449674694', 'ASE0326727870655', 'ASE1466450566875', 'ASE8823669482938', 'ASE1410327414953', 'ASE3000886724539', 'ASE3134909695596', 'ASE6281447143769', 'ASE2315171213450', 'ASE8328084902303', 'ASE0631599434028', 'ASE5947831266534', 'ASE3245117383937', 'ASE9542954424165', 'ASE5649988651856', 'ASE9497688526316', 'ASE3054625915752', 'ASE6919003155654', 'ASE8323902851079', 'ASE7885083207836', 'ASE7929014365126', 'ASE8834969890139', 'ASE4597548547204', 'ASE0763443098635', 'ASE1045401724639', 'ASE6865589304937', 'ASE9169000018855', 'ASE2113848047865', 'ASE1105943045548', 'ASE7833745892544', 'ASE9676746320774', 'ASE4657980942062', 'ASE6316651757939', 'ASE8767127738269', 'ASE6805301614934', 'ASE4387988658875', 'ASE3098983888155', 'ASE1012055554399', 'ASE2752520489804', 'ASE1945332474750', 'ASE2974082123775', 'ASE1986696392953', 'ASE3043292190635', 'ASE4060433014984', 'ASE5731832224595', 'ASE5277752855192', 'ASE1669828283387', 'ASE6506219844390', 'ASE0974163200220', 'ASE7660118849271', 'ASE4104210425237', 'ASE9715264857282', 'ASE4962169223618', 'ASE3265393425283', 'ASE8005637401139', 'ASE7113043138746', 'ASE7382355054442', 'ASE1582467469783', 'ASE3083856662971', 'ASE7989305341024', 'ASE5352914311558', 'ASE4338586199368', 'ASE9232455930558', 'ASE7366906098184', 'ASE4350615052801', 'ASE2253033444493', 'ASE4436057538795', 'ASE5895046017029', 'ASE4622084293648', 'ASE0526953802914', 'ASE2293758797519', 'ASE1204836179378', 'ASE0020988274382', 'ASE6022886144698', 'ASE3050900134503', 'ASE9434550477719', 'ASE8025822520907', 'ASE7857356499565', 'ASE4485082584094', 'ASE0511658688266', 'ASE0011209114986', 'ASE2368335542734', 'ASE0877946065707', 'ASE9848829839822', 'ASE9764815708519', 'ASE6524277725615', 'ASE2351916898223', 'ASE1296930761131', 'ASE9921836854374', 'ASE5489807384689', 'ASE8724343925182', 'ASE5535370469139', 'ASE5301839969449', 'ASE5241485136933', 'ASE4214085573452', 'ASE2495260864592', 'ASE4270332401323', 'ASE5378674651879', 'ASE7530829283452', 'ASE2158617678286', 'ASE9549854318499', 'ASE4030870802188', 'ASE0058999011600', 'ASE5994821173700', 'ASE3202026543567', 'ASE3398706507914', 'ASE4334175522928', 'ASE6608122317360', 'ASE4605387792133', 'ASE9013998896227', 'ASE4727094496826', 'ASE1878565298830', 'ASE0180048948611', 'ASE3525350368097', 'ASE9024502144769', 'ASE1635313216988', 'ASE4676053594716', 'ASE0390280367734', 'ASE6157448004846', 'ASE7693408663475', 'ASE9871216426528', 'ASE7351206280419', 'ASE1294722283727', 'ASE0377743514144', 'ASE1024462372289', 'ASE3735359729385', 'ASE4624967774632', 'ASE1326285973282', 'ASE9126649307674', 'ASE1133152403080', 'ASE2926690737848', 'ASE8801339582217', 'ASE0978811082522', 'ASE1477860474404', 'ASE2133093519546', 'ASE2127898298899', 'ASE1926566512503', 'ASE6826992379344', 'ASE9279855637181'];
        $arr7 = ['ASE4559537437307', 'ASE6643264812873', 'ASE2088548070671', 'ASE2768121359515', 'ASE4347183628375', 'ASE1887121746557', 'ASE9909239093981', 'ASE9677297828950', 'ASE2590322591872', 'ASE3240553688647', 'ASE0441146955782', 'ASE7616237818758', 'ASE0410493288690', 'ASE4589639253227', 'ASE0029755292802'];
        $arr8 = ['ASE1360979477020', 'ASE5127094182393', 'ASE5874083528630'];
        $arr = [];
        switch ($option_arr) {
            case 1:
                $arr = $arr1;
                break;
            case 2:
                $arr = $arr2;
                break;
            case 3:
                $arr = $arr3;
                break;
            case 4:
                $arr = $arr4;
                break;
            case 5:
                $arr = $arr5;
                break;
            case 6:
                $arr = $arr6;
                break;
            case 7:
                $arr = $arr7;
                break;
            case 8:
                $arr = $arr8;
                break;
        }

        $packages = Package::where(function ($q) use ($warehouse) {
            $q->orWhere('packages.warehouse_id', $warehouse->id)->orWhere('packages.country_id', $warehouse->country_id);
        })->whereNull('packages.ukr_express_id')->whereIn('packages.custom_id', $arr);//->whereNull('bot_comment');
        $packages->whereRaw("(packages.ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',packages.ukr_express_error_at))>3*3600)");
        $packages = $packages->leftJoin('users as uu', 'packages.user_id', 'uu.id');
        $packages->whereRaw('(uu.ukr_express_id is not null)');
        $packages->select('packages.*', 'uu.id as uu_id');
        $packages = $packages->limit($option_limit)->get();
        if (count($packages) > 0) {
            $this->info(count($packages) . " packages to add");
        }
        $cnt = 1;
        foreach ($packages as $package) {
            $ldate = date('Y-m-d H:i:s');
            $this->line($ldate . " " . $cnt . " " . $package->tracking_code);
            $cnt++;
            $this->package_add($package);
        }
    }

    public function packages_add_to_ukr_no_declared_price()
    {
        $ldate = date('Y-m-d H:i:s');
        //$this->info("===== add new packages =====");
        //$this->line("");
        $warehouse = $this->warehouse;
        $packages = Package::where(function ($q) use ($warehouse) {
            $q->orWhere('packages.warehouse_id', $warehouse->id)->orWhere('packages.country_id', $warehouse->country_id);
        })->whereNull('packages.ukr_express_id')->where('bot_comment', 'no declared price');
        $packages->whereRaw("(packages.ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',packages.ukr_express_error_at))>3*3600)");
        $packages = $packages->leftJoin('users as uu', 'packages.user_id', 'uu.id');
        $packages->whereRaw('(uu.ukr_express_id is not null)');
        $packages->select('packages.*', 'uu.id as uu_id');
        $packages = $packages->limit(100)->get();
        if (count($packages) > 0) {
            $this->info(count($packages) . " packages to add");
        }
        $cnt = 1;
        foreach ($packages as $package) {
            $ldate = date('Y-m-d H:i:s');
            $this->line($ldate . " " . $cnt . " " . $package->tracking_code);
            $cnt++;
            $this->package_add($package);
        }
    }

    public function packages_update_from_ukr()
    {
        $ldate = date('Y-m-d H:i:s');
        $warehouse = $this->warehouse;

        $packages = Package::where(function ($q) use ($warehouse) {
            $q->orWhere('warehouse_id', $warehouse->id)->orWhere('country_id', $warehouse->country_id);
        })->whereNotNull('ukr_express_id')->whereNotNull('user_id');//->whereNull('deleted_at');
        //$packages = Package::whereRaw('warehouse_id='.$warehouse->id.' or country_id='. $warehouse->country_id)->whereNotNull('ukr_express_id')->whereNotNull('user_id')->whereNull('deleted_at');
        $packages->whereRaw('(weight is null or weight <= 0)');
        $packages->whereRaw('(weight_goods is null or weight_goods <= 0)');
        //$packages->whereRaw("((status = 6 and ukr_express_status=1) or (status in (0,6) and (bot_comment='notpacked' or bot_comment='Added' or ukr_express_status>=100)))");
        $packages->whereRaw("((status in (0,6) and ukr_express_status>0) or (status in (0,6) and (bot_comment='notpacked' or bot_comment='Added' or ukr_express_status>=100)))");
        $packages->whereRaw("(ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',ukr_express_error_at))>3*3600)");
        $packages = $packages->limit(1000)->get();
        if (count($packages) > 0) {
            //$this->info(count($packages)." packages to update");
        }
        $cnt = 1;
        foreach ($packages as $package) {
            $ldate = date('Y-m-d H:i:s');
            if ($this->package_update($package)) {
                $this->line($ldate . " " . $cnt . " " . $package->tracking_code . " " . $package->warehouse_id . " " . $package->deleted_at);
                $cnt++;
            }
            sleep(1);
        }
    }

    public function packages_add_to_ukr()
    {
        //$this->line("");
        $ldate = date('Y-m-d H:i:s');
        //$this->info("===== add new packages =====");
        //$this->line("");
        $warehouse = $this->warehouse;

        $packages = Package::where(function ($q) use ($warehouse) {
            $q->orWhere('packages.warehouse_id', $warehouse->id)->orWhere('packages.country_id', $warehouse->country_id);
        })->whereNull('packages.ukr_express_id')->whereNotNull('packages.user_id');
//        $packages = Package::where('custom_id','ASE0960854047355');
        $packages->whereRaw("((packages.status = 6 and (bot_comment is NULL or packages.ukr_express_status=1)) or (packages.status in (0,6) and (packages.bot_comment='notpacked' or packages.ukr_express_status>=100)))");
        $packages->whereRaw("(packages.ukr_express_error_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',packages.ukr_express_error_at))>3*3600)");
        $packages = $packages->leftJoin('users as uu', 'packages.user_id', 'uu.id');
        //$packages = $packages->leftJoin('users as ud','uu.parent_id','ud.id');
        //$packages->whereRaw('((uu.ukr_express_id is not null) or ((ud.id is not null) and (ud.ukr_express_id is not null)))');
        $packages->whereRaw('(uu.ukr_express_id is not null)');
        //$packages->select('packages.*','uu.id as uu_id','ud.id as ud_id');
        $packages->select('packages.*', 'uu.id as uu_id');
        $packages = $packages->limit(100)->get();
        //})->where('tracking_code','37373848484922')->limit(1)->get();
        if (count($packages) > 0) {
            $this->info(count($packages) . " packages to add");
        }
        $cnt = 1;
        foreach ($packages as $package) {
            $ldate = date('Y-m-d H:i:s');
            $this->line($ldate . " " . $cnt . " " . $package->tracking_code . " " . $package->warehouse_id . " " . $package->deleted_at);
            $cnt++;
            //continue;
            $this->package_add($package);
        }
    }

    public function package_list($offset, $limit, $filter, $customer_id)
    {
        echo " Getting tracks from " . $offset . " limit " . $limit . " filter " . $filter . " customer_id " . $customer_id . "\n";
        $tracks = $this->ue->track_list($offset, $limit, $filter, $customer_id);
        $cnt = 0;
        if (!$tracks)
            echo "No tracks\n";
        else
            foreach ($tracks as $track) {
                $cnt++;
                echo $cnt . " ID:" . $track->id . " C:" . $track->customer_id . " P:" . $track->parcel_id . " " . $track->number . " " . date('Y-m-d H:i:s', $track->receiving_info->timestamp);
                //print_r($track);
                echo "\n";
            }
    }

    public function parcel_list($offset, $limit, $container_id, $airbox_id, $in_warehouse)
    {
        echo " Getting parcels from " . $offset . " limit " . $limit . " container_id " . $container_id . " airbox_id " . $airbox_id . " in_warehouse " . $in_warehouse . "\n";
        $parcels = $this->ue->parcel_list($offset, $limit, $container_id, $airbox_id, $in_warehouse);
        $cnt = 0;
        $total_weight = 0;
        foreach ($parcels as $parcel) {
            $cnt++;
            echo $cnt . " P:" . $parcel->id . " C:" . $parcel->customer_id;
            $package = Package::where('ukr_express_parcel_id', $parcel->id)->first();
            if ($package) {
                echo " ID:" . $package->id . " CID:" . $package->custom_id;
                if ($package->parcel && $package->bag && $package->bag->count()) {
                    $bag = $package->bag->first();
                    echo " BAG ID:" . $bag->id . " BAG:" . $bag->custom_id;
                } else {
                    echo " NO BAG";
                }
            }
            $weight = $parcel->weight_in_grams / 100;
            $total_weight += $weight;
            //print_r($track);
            echo " w:" . $weight . "\n";
        }
        echo "Total weight: " . $total_weight . "\n";
    }


    public function packages_sync_from_ukr()
    {
        $tracks = $this->ue->track_list();
        foreach ($tracks as $track) {
            echo $cnt . " ";
            print_r($track);
            echo "\n";
            continue;
            $package = Package::where('tracking_code', $track->number)->first();
            if (!$package) continue;
            $package->ukr_express_id = $track->id;
            if ($track->weight_in_grams && $track->weight_in_grams > 0) {
                $package->weight = $track->weight_in_grams / 1000;
            }
            $package->save();
        }
    }
}
