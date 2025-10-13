<?php

namespace App\Http\Controllers\Front;

use App\Http\Requests;
use App\Models\Bag;
use App\Models\ExclusiveLock;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Parcel;
use App\Models\UkrExpressModel;
use App\Models\User;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Response;

/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Front
 */
class UExpressController extends MainController
{
    /**
     * @return JsonResponse
     */
    protected $ue = null;
    protected $warehouse;
    protected $sendTelegram = true;

    public function testget()
    {
        $params = Request::all();
        $ldate = date('Y-m-d H:i:s');
        $ip = Request::ip();
        $str = $ldate . "\n" . "  GET from ip:" . $ip . "\n";
        $str .= "  data:" . json_encode($params) . "\n";
        file_put_contents('/var/log/ase_uexpress.log', $str, FILE_APPEND);
        return Response::json([
            'res' => 'Ok',
            'type' => 'GET'
        ]);
    }

    public function err($message)
    {
        $ldate = date('Y-m-d H:i:s');
        file_put_contents('/var/log/ase_uexpress2_error.log', $ldate . " api " . $message . "\n", FILE_APPEND);
    }

    public function log($message)
    {
        file_put_contents('/var/log/ase_uexpress2_api.log', $message . "\n", FILE_APPEND);
    }

    public function callback()
    {
        $this->warehouse = Warehouse::find(11);
        $this->ue = new UkrExpressModel();
        $this->ue->doLog = false;
        $this->ue->doErr = true;
        $params = Request::all();
        $ldate = date('Y-m-d H:i:s');
        $ip = Request::ip();
        $str = $ldate . "\n" . "  POST from ip:" . $ip . "\n";
        $str .= "  data:" . json_encode($params);
        $this->log($str);
        $eventName = null;
        $eventName = Request::get('event_name');
        if ($eventName == "tracking-received") {
            $this->packageUpdate($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), Request::get('parcel_id'));
        } else if ($eventName == "tracking-customer-assigned") {
            $this->packageUpdate($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), Request::get('parcel_id'), 1);
        } else if ($eventName == "tracking-packed") {
            $this->packageUpdate($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), Request::get('parcel_id'), 2);
        } else if ($eventName == "tracking-reweighed") {
            $this->packageUpdate($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), Request::get('parcel_id'), 9);
        }
        //else if($eventName=="load-parcel") {
        //$this->parcelLoaded(\Request::get('parcel_id'),\Request::get('new_container'));
        //}
        else if ($eventName == "parcel-loaded-to-container") {
        } else if ($eventName == "parcel-loaded-to-airbox") {
            //echo $eventName." OK\n";
            $this->parcelLoadedToAirbox($eventName, Request::get('parcel_id'), Request::get('airbox_id'), Request::get('new_airbox'));
        } else if ($eventName == "airbox-loaded-to-container") {
            //echo $eventName." OK\n";
            $this->airboxLoadedToContainer($eventName, Request::get('airbox_id'), Request::get('container_id'), Request::get('new_container'));
        } else if ($eventName == "tracking-unpacked") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), '', 1);
        } else if ($eventName == "tracking-damaged") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), '', 2);
        } else if ($eventName == "tracking-deleted") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), '', 3);
        } else if ($eventName == "tracking-unassigned") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), '', 3);
        } else if ($eventName == "tracking-utilized") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), '', 3);
        } else if ($eventName == "tracking-attention") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), '', 3);
        } else if ($eventName == "tracking-forbidden") {
            $this->packageWarning($eventName, Request::get('tracking_id'), Request::get('customer_id'), Request::get('tracking_number'), Request::get('forbidden_description'), 3);
        } else {
            $this->log("  unknown eventName " . $eventName);
            $this->err("  unknown eventName " . $eventName . " " . json_encode($params));
        }
        return Response::json([
            'res' => 'Ok',
            'type' => 'POST'
        ]);
    }

    public function packageWarning($eventName, $tracking_id, $customer_id, $tracking_number, $description, $status = 0)
    {
        $ldate = date('Y-m-d H:i:s');
        $message = "🛑 Warning " . $eventName . " " . $description . " " . $tracking_number . " from Ukraine Express\n";
        $log_message = "  warning package " . $eventName . " " . $description . " " . $tracking_id . " " . $tracking_number;
        $item = Package::withTrashed()->where('ukr_express_id', $tracking_id)->first();
        if ($item) {
            $item->bot_comment = $eventName;
            if (!empty($description))
                $item->bot_comment .= ' ' . $description;
            $item->ukr_express_status = 120 + $status;
            if ($eventName != "tracking-attention")
                $item->ukr_express_error_at = $ldate;
            if ($eventName == 'tracking-deleted') {
                $item->ukr_express_deleted = 1;
                if (!$item->invoice) {
                    $item->deleted_at = $ldate;
                    $item->bot_comment .= " DELETED";
                    $message .= " <b>DELETED</b>\n";
                    $log_message .= " DELETED";
                }
            }
            if ($eventName == 'tracking-unassigned') {
                $item->ukr_express_unassigned = 1;
                if (!$item->invoice) {
                    $item->deleted_at = $ldate;
                    $item->bot_comment .= " UNASSIGNED";
                    $message .= " <b>UNASSIGNED</b>\n";
                    $log_message .= " UNASSIGNED";
                }
            }
            if ($eventName == 'tracking-utilized') {
                $item->ukr_express_utilized = 1;
                if (!$item->invoice) {
                    $item->deleted_at = $ldate;
                    $item->bot_comment .= " UTILIZED";
                    $message .= " <b>UTILIZED</b>\n";
                    $log_message .= " UTILIZED";
                }
            }
            $item->save();
            if ($item->user)
                $message .= " <b>" . $item->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $item->user->customer_id . "'>" . $item->user->customer_id . "</a>)";
            $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $item->custom_id . "'>" . $item->tracking_code . "</a>\n";
        }
        $this->log($log_message);
        $this->err($log_message);
        if ($this->sendTelegram && ($eventName != "tracking-attention")) sendTGMessage($message);
    }

    public function airboxLoadedToContainer($eventName, $airbox_id, $container_id, $new_container)
    {
        if (empty($container_id)) {
            $this->log("  Empty container_id ");
            $this->err("  Empty container_id ");
            return;
        }
        if (empty($airbox_id)) {
            $this->log("  Empty airbox_id ");
            $this->err("  Empty airbox_id ");
            return;
        }
        $u_airbox = $this->ue->airbox_get($airbox_id);
        if (!$u_airbox) {
            $this->log("  Airbox not exists in ukr express " . $airbox_id);
            $this->err("  Airbox not exists in ukr express " . $airbox_id);
            return;
        }

        //Create or get parcel
        //$container_id=$u_airbox->container_id;
        $parcel_name = 'USA_TEMP';
        $parcel = NULL;
        if ($container_id) {
            $parcel_name = 'USA_' . $container_id;
            $parcel = Parcel::where('ukr_express_container_id', $container_id)->where('warehouse_id', $this->warehouse->id)->first();
            if (!$parcel) {
                $parcel = Parcel::where('custom_id', $parcel_name)->where('warehouse_id', $this->warehouse->id)->first();
                if ($parcel) {
                    $parcel->ukr_express_container_id = $container_id;
                    $parcel->save();
                }
            }
            if (!$parcel) {
                $parcel = Parcel::create([
                    'custom_id' => $parcel_name,
                    'warehouse_id' => $this->warehouse->id,
                    'ukr_express_container_id' => $container_id
                ]);
                $parcel->ukr_express_container_id = $container_id;
                $this->log("  Parcel created " . $parcel_name . " " . $container_id);
                $parcel->save();
            }
        } else {
            if (!$parcel) {
                $parcel = Parcel::where('custom_id', $parcel_name)->where('warehouse_id', $this->warehouse->id)->first();
            }
            if (!$parcel) {
                $parcel = Parcel::create([
                    'custom_id' => $parcel_name,
                    'warehouse_id' => $this->warehouse->id,
                ]);
                $this->log("  Parcel created " . $parcel_name);
                $parcel->save();
            }
        }
        if (!$parcel) {
            $this->log("  Cannot create or get parcel name: " . $parcel_name . " container_id: " . $container_id);
            $this->err("  Cannot create or get parcel name: " . $parcel_name . " container_id: " . $container_id);
            return;
        }
        //----

        //Create or get bag
        $bag_name = $u_airbox->code;
        $bag = Bag::where('ukr_express_airbox_id', $airbox_id)->first();
        if (!$bag) {
            $bag = Bag::where('custom_id', $bag_name)->first();
        }
        if (!$bag) {
            $bag = Bag::create([
                'custom_id' => $bag_name,
            ]);
        }
        if (!$bag) {
            $this->log("  Cannot create or get bag name: " . $bag_name . " airbox_id: " . $airbox_id);
            $this->err("  Cannot create or get bag name: " . $bag_name . " airbox_id: " . $airbox_id);
            return;
        }
        $bag->custom_id = $bag_name;
        $bag->ukr_express_airbox_id = $airbox_id;
        $bag->parcel_id = $parcel->id;
        $bag->save();
        $this->log("  Bag  " . $bag_name . " airbox_id: " . $airbox_id);
        //-----

        //insert packages into parcel
        $packages = DB::select('select package_id from bag_package where bag_id=' . $bag->id);
        //$packages = Package::select('packages.*')->leftJoin('bag_package','packages.id','bag_package.package_id')->where('bag_package.bag_id', $bag->id);
        if (!$packages || count($packages) <= 0) {
            $this->log("  No packages in bag with airbox_id: " . $airbox_id);
            $this->err("  No packages in bag with airbox_id: " . $airbox_id);
            return;
        }
        //$this->log("list packages");
        foreach ($packages as $package) {
            //$this->log(" package ".$package->package_id);
            //DB::delete("delete from bag_package where package_id=?",[$package->id]);
            DB::delete("delete from parcel_package where package_id=?", [$package->package_id]);
            //DB::insert("insert into bag_package (bag_id,package_id) values (?,?)",[$bag->id,$package->id]);
            DB::insert("insert into parcel_package (parcel_id,package_id) values (?,?)", [$parcel->id, $package->package_id]);
            //------
            DB::update("update packages set ukr_express_status=6,bot_comment=? where id=?", [$eventName, $package->package_id]);
        }

    }

    public function parcelLoadedToAirbox($eventName, $parcel_id, $airbox_id, $new_airbox)
    {
        if (empty($airbox_id)) {
            $this->log("  Empty airbox_id ");
            $this->err("  Empty airbox_id ");
            return;
        }
        $u_airbox = $this->ue->airbox_get($airbox_id);
        if (!$u_airbox) {
            $this->log("  Airbox not exists in ukr express " . $airbox_id);
            $this->err("  Airbox not exists in ukr express " . $airbox_id);
            return;
        }

        //Create or get parcel
        $container_id = NULL;
        if (isset($u_airbox->container_id))
            $container_id = $u_airbox->container_id;
        $parcel_name = 'USA_TEMP';
        $parcel = NULL;
        if ($container_id) {
            $parcel_name = 'USA_' . $container_id;
            $parcel = Parcel::where('ukr_express_container_id', $container_id)->where('warehouse_id', $this->warehouse->id)->first();
            if (!$parcel) {
                $parcel = Parcel::where('custom_id', $parcel_name)->where('warehouse_id', $this->warehouse->id)->first();
                if ($parcel) {
                    $parcel->ukr_express_container_id = $container_id;
                    $parcel->save();
                }
            }
            if (!$parcel) {
                $parcel = Parcel::create([
                    'custom_id' => $parcel_name,
                    'warehouse_id' => $this->warehouse->id,
                    'ukr_express_container_id' => $container_id
                ]);
                $parcel->ukr_express_container_id = $container_id;
                $this->log("  Parcel created " . $parcel_name . " " . $container_id);
                $parcel->save();
            }
        } else {
            if (!$parcel) {
                $parcel = Parcel::where('custom_id', $parcel_name)->where('warehouse_id', $this->warehouse->id)->first();
            }
            if (!$parcel) {
                $parcel = Parcel::create([
                    'custom_id' => $parcel_name,
                    'warehouse_id' => $this->warehouse->id,
                ]);
                $this->log("  Parcel created " . $parcel_name);
                $parcel->save();
            }
        }
        if (!$parcel) {
            $this->log("  Cannot create or get parcel name: " . $parcel_name . " container_id: " . $container_id);
            $this->err("  Cannot create or get parcel name: " . $parcel_name . " container_id: " . $container_id);
            return;
        }
        //----

        //Create or get bag
        $bag_name = $u_airbox->code;
        $bag = Bag::where('ukr_express_airbox_id', $airbox_id)->first();
        if (!$bag) {
            $bag = Bag::where('custom_id', $bag_name)->first();
        }
        if (!$bag) {
            $bag = Bag::create([
                'parcel_id' => $parcel->id,
                'custom_id' => $bag_name,
            ]);
        }
        if (!$bag) {
            $this->log("  Cannot create or get bag name: " . $bag_name . " airbox_id: " . $airbox_id);
            $this->err("  Cannot create or get bag name: " . $bag_name . " airbox_id: " . $airbox_id);
            return;
        }
        $bag->custom_id = $bag_name;
        $bag->ukr_express_airbox_id = $airbox_id;
        $bag->parcel_id = $parcel->id;
        $bag->save();
        $this->log("  Bag  " . $bag_name . " airbox_id: " . $airbox_id);
        //-----

        //insert package into bag & parcel
        if (empty($parcel_id)) {
            $this->log("  Empty parcel_id ");
            $this->err("  Empty parcel_id ");
            return;
        }
        $package = Package::where('ukr_express_parcel_id', $parcel_id)->first();
        if (!$package) {
            $this->log("  Cannot get package with ukr_express_parcel_id: " . $parcel_id);
            $this->err("  Cannot get package with ukr_express_parcel_id: " . $parcel_id);
            //$u_parcel=$this->ue->parcel_get(
            return;
        }
        DB::delete("delete from bag_package where package_id=?", [$package->id]);
        DB::delete("delete from parcel_package where package_id=?", [$package->id]);
        DB::insert("insert into bag_package (bag_id,package_id) values (?,?)", [$bag->id, $package->id]);
        DB::insert("insert into parcel_package (parcel_id,package_id) values (?,?)", [$parcel->id, $package->id]);
        //------
        $package->bot_comment = $eventName;
        $package->ukr_express_status = 5;
        $package->save();

    }

    public function parcelLoaded($parcel_id, $new_container)
    {
        $bag = Bag::where('ukr_express_parcel_id', $parcel_id)->first();
        if (!$bag) {
            $this->log("  Cannot find parcel with id " . $parcel_id);
            return;
        }
        $customer_id = '0';
        $ue_parcel = $this->ue->parcel_get($customer_id, $parcel_id);
        $parcel_name = 'USA_TEMP';
        $container_id = 0;
        if ($ue_parcel && $ue_parcel->container_id && $ue_parcel->container_id > 0) {
            $parcel_name = 'USA_' . $ue_parcel->container_id;
            $container_id = $ue_parcel->container_id;
            $this->log("  container_id " . $container_id);
        } else {
            $this->log("  no parcel or container_id ");
        }
    }

    public function packageUpdate($eventName, $tracking_id, $customer_id, $tracking_number, $parcel_id, $status = 0)
    {
        $ldate = date('Y-m-d H:i:s');
        $track = $this->ue->track_get($tracking_id, $customer_id);
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
            $this->log("  Cannot get package " . $tracking_id . " " . $tracking_number . " " . $eventName . " from Ukraine Express!");
            $this->err("  Cannot get package " . $tracking_id . " " . $tracking_number . " " . $eventName . " from Ukraine Express!");
            $message = "🛑 Eror add/update from Ukraine Express\n";
            $message .= "Error: " . "  Cannot get package " . $tracking_id . " " . $tracking_number . " " . $eventName . " from Ukraine Express!";
            /*$item->bot_comment=$eventName." Cannot get package from ukr express";
            $item->ukr_express_status=101;
                $item->ukr_express_error_at=$ldate;
            $item->save();*/
            if ($this->sendTelegram) sendTGMessage($message);
            return;
        }
        $lock = new ExclusiveLock($tracking_id);
        $lock->lock();
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
        //$item=Package::withTrashed()->where('ukr_express_id',$tracking_id)->first();
        $item = Package::where('ukr_express_id', $tracking_id)->first();
        if ($tracking_number && !$item) {
            //$item=Package::withTrashed()->where('tracking_code',$tracking_number)->first();
            $item = Package::where('tracking_code', $tracking_number)->first();

            if (!$item) {
                $code = $tracking_number;
                //$item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                $item = Package::where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                $len = strlen($code) + 1;
                if (!$item && $len > 10) {
                    //$item = Package::withTrashed()->whereRaw("length(tracking_code)>=8 and instr('".$code."',tracking_code)=greatest(".$len."-length(tracking_code),10)")->orderBy('deleted_at', 'asc')->first();
                    $item = Package::whereRaw("length(tracking_code)>=8 and instr('" . $code . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10)")->orderBy('deleted_at', 'asc')->first();
                }

                if (!$item && strlen($code) >= 10) {
                    $start = -1 * strlen($code) + 1;
                    $cnt = 0;
                    for ($i = $start; $i <= -8; $i++) {
                        $code = substr($code, $i);
                        //$item = Package::withTrashed()->where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                        $item = Package::where("tracking_code", "like", $code . "%")->orderBy('deleted_at', 'asc')->first();
                        //$this->warn('Checking .. ' . $code);

                        if ($item) {
                            break;
                        }
                        $cnt++;
                        if ($cnt >= 8) break;
                    }
                }
            }
            if ($item) {
                $item->ukr_express_id = $tracking_id;
            }
        }
        if ($item) {
            if ($item->deleted_at)
                $item->deleted_at = NULL;
        }
        $user = null;
        $new_package = false;
        $new_user = false;
        //check here if same tracking exists in the database if it exists add to condition
        $tracking_check = Package::where('tracking_code', $tracking_number)->whereNotNull('deleted_at')->first();
        if (!$item && !$tracking_check) {
            $this->log("  Cannot find package " . $track->number . " (" . $tracking_id . ") to update inserting...");
            $item = new Package();
            $new_package = true;
            $item->warehouse_id = $this->warehouse->id;
            if ($tracking_number) {
                $item->tracking_code = $tracking_number;
            }
            $item->ukr_express_id = $tracking_id;
        } else {
            $user = $item->user;
            if ($user) {
                if ($customer_id && ($user->ukr_express_id != $customer_id)) {
                    //$this->log($track->customer_id."  ". $customer_id);
                    $this->log("  diff customers " . $user->ukr_express_id . "  " . $customer_id);
                    //if(($track->customer_id != $customer_id) && (!$this->ue->change_customer($item,$customer_id))) {
                    if (!$this->ue->change_customer($item, $customer_id)) {
                        $this->log("  Cannot change customer " . $track->number . " (" . $tracking_id . "," . $customer_id . ") to " . $user->ukr_express_id);
                        $message = "🛑 Eror add/update from Ukraine Express\n";
                        if ($item->user)
                            $message .= " <b>" . $item->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $item->user->customer_id . "'>" . $item->user->customer_id . "</a>)";
                        $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $item->custom_id . "'>" . $item->tracking_code . "</a>\n";
                        $message .= "Error: " . "  Cannot change customer " . $track->number . " (" . $tracking_id . "," . $customer_id . ") to " . $user->ukr_express_id . "\n";
                        $item->bot_comment = $eventName . " Cannot change customer";
                        $item->ukr_express_status = 102;
                        $item->ukr_express_error_at = $ldate;
                        $item->save();
                        if ($this->sendTelegram) sendTGMessage($message);
                        //return;
                    } else {
                        $customer_id = $user->ukr_express_id;
                    }
                }
            } else {
                $new_user = true;
            }
        }

        if ($customer_id) {
            //$user=User::withTrashed()->where('ukr_express_id',$customer_id)->first();
            $user = User::where('ukr_express_id', $customer_id)->first();
            if (!$user) {
                $this->log("  Cannot find user of package " . $track->number . " (" . $tracking_id . "," . $customer_id . ") to update");
                $this->err("  Cannot find user of package " . $track->number . " (" . $tracking_id . "," . $customer_id . ") to update");
                $message = "🛑 Eror add/update from Ukraine Express\n";
                if ($item->user)
                    $message .= " <b>" . $item->user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $item->user->customer_id . "'>" . $item->user->customer_id . "</a>)";
                $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $item->custom_id . "'>" . $item->tracking_code . "</a>\n";
                $message .= "Error: " . "  Cannot find user of package " . $track->number . " (" . $tracking_id . "," . $customer_id . ") to update" . "\n";
                $item->ukr_express_error_at = $ldate;
                $item->ukr_express_status = 103;
                $item->bot_comment = $eventName . " Cannot find user of package";
                $item->save();
                if ($this->sendTelegram) sendTGMessage($message);
                $lock->unlock();
                return;
            } else {
                if (is_null($item->user_id)) {
                    $item->user_id = $user->id;
                }
            }
        }
        //if($status==0 || $new_package) {
        if ($item->status == 6 || $new_package || $new_user) {
            if ($new_package)
                $message = "✅ Package added from Ukraine Express";
            else
                $message = "✅ Package updated from Ukraine Express";
            $message .= " " . $eventName . "\n";
            if ($user)
                $message .= " <b>" . $user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $user->customer_id . "'>" . $user->customer_id . "</a>)";
            $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $track->number . "'>" . $track->number . "</a>\n";
            if ($new_weight && ($item->getWeight() != $new_weight))
                $message .= " updated weight from: " . $item->getWeight() . " to " . $new_weight;
            if ($item->user_id && !$item->show_label) {
                $item->show_label = true;
            }

            $item->u_tracing_code = $item->tracking_code;
            if ($new_weight || ($eventName != 'tracking-customer-assigned')) {
                $item->status = 0;
                // Send Notification
                Notification::sendPackage($item->id, '0');
                /* Send notification */
                $message .= " notification sent to user " . ($user ? $user->full_name : 'NotFound');
                if ($this->sendTelegram)
                    sendTGMessage($message);
            }
        }
        if ($status == 2) {
            if ($parcel_id) {
                $item->ukr_express_parcel_id = $parcel_id;
            } else {
                $item->ukr_express_parcel_id = $track->parcel_id;
            }
            $old_weight = $item->getWeight();
            if (
                ((!$old_weight && $new_weight) || ($new_weight && abs($new_weight - $old_weight) >= 0.01))
                || ((!$item->width && $new_width) || ($new_width && abs($new_width - $item->width) >= 0.01))
                || ((!$item->length && $new_length) || ($new_length && abs($new_length - $item->length) >= 0.01))
                || ((!$item->height && $new_height) || ($new_height && abs($new_height - $item->height) >= 0.01))
            ) {
                if ($new_weight) {
                    $item->weight = $new_weight;
                    $item->weight_goods = $new_weight;
                }
                if ($new_width)
                    $item->width = $new_width;
                if ($new_length)
                    $item->length = $new_length;
                if ($new_height)
                    $item->height = $new_height;
                $item->delivery_price = NULL;
                $item->delivery_price_azn = NULL;
                $item->delivery_price_usd = NULL;
            }
        }
        if ($item->status == 6 || $new_package || $status == 0 || $status == 1 || $status == 9 || !$item->weight || !$item->weight_goods) {
            if ($new_weight) {
                $old_weight = $item->getWeight();
                $item->weight = $new_weight;
                $item->weight_goods = $new_weight;
                if ($new_width)
                    $item->width = $new_width;
                if ($new_length)
                    $item->length = $new_length;
                if ($new_height)
                    $item->height = $new_height;
                $item->delivery_price = NULL;
                $item->delivery_price_azn = NULL;
                $item->delivery_price_usd = NULL;
                if ($new_package)
                    $this->log("  added package " . $track->number . " (" . $tracking_id . "," . $customer_id . ") weight from " . $old_weight . " to " . $new_weight . " parcel " . $track->parcel_code);
                else
                    $this->log("  updated package " . $track->number . " (" . $tracking_id . "," . $customer_id . ") weight from " . $old_weight . " to " . $new_weight . " parcel " . $track->parcel_code);
                if ($status == 9) {
                    $message = "✅ Package WEIGHT updated from Ukraine Express";
                    $message .= " " . $eventName . "\n";
                    if ($user)
                        $message .= " <b>" . $user->full_name . "</b>  (<a href='https://admin." . env('DOMAIN_NAME') . "/users?q=" . $user->customer_id . "'>" . $user->customer_id . "</a>)";
                    $message .= "   <a href='https://admin." . env('DOMAIN_NAME') . "/packages?q=" . $track->number . "'>" . $track->number . "</a>\n";
                    if ($new_weight && ($old_weight != $new_weight))
                        $message .= " updated weight from: " . $old_weight . " to " . $new_weight;
                    if ($this->sendTelegram) sendTGMessage($message);
                }
            }
            if ($new_fee)
                $item->additional_delivery_price = $new_fee;
        } else {
            $this->log("  updated package " . $track->number . " (" . $tracking_id . "," . $customer_id . ") parcel " . $track->parcel_code);
        }
        $item->tracking_code = $track->number;
        $item->bot_comment = $eventName;
        $item->ukr_express_status = $status + 2;
        $item->ukr_express_error_at = null;
        $item->save();
        $lock->unlock();
    }
}
