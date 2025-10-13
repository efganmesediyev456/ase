<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Bag;
use App\Models\Extra\Logic;
use App\Models\LogicSync;
use App\Models\Package;
use App\Models\Parcel;
use Auth;
use DB;
use View;
use function request;

class LogicSyncController extends Controller
{
    protected $list = [
        'num' => [
            'label' => '#',
        ],
        'bag_name' => [
            'label' => 'LOGIC BAG',
        ],
        'package_name' => [
            'label' => 'LOGIC CWB',
        ],
        'sender' => [
            'label' => 'LOGIC SENDER',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'package.parcel' => [
            'type' => 'parcel',
            'label' => 'PARCEL',
        ],
        'package.bag' => [
            'type' => 'bag',
            'label' => 'BAG',
        ],
        'package.custom_id' => [
            'label' => 'CWB No',
        ],
        "package.tracking_code" => [
            'label' => 'Tracking #',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'package.parcel' => [
            'type' => 'parcel',
            'label' => 'PARCEL',
        ],
        'package.bag' => [
            'type' => 'bag',
            'label' => 'BAG',
        ],
        'package.custom_id' => [
            'label' => 'CWB No',
        ],
        "package.tracking_code" => [
            'label' => 'Tracking #',
        ],
        'package.user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],

        'package.status_with_label' => [
            'label' => 'Status',
        ],

        'package.warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
    ];

    protected $modelName = 'LogicSync';
    protected $syncs = [];
    protected $ready_sync_count = 0;
    protected $bag_names = [];
    protected $bag_syncs = [];
    protected $parcel_name = '';

    public function __construct()
    {
        parent::__construct();
        View::share('_list', $this->list);
        //\View::share('userPackageAction', $this->userPackageAction);
        //\View::share('donePackageAction', $this->donePackageAction);
    }

    public function index()
    {
        $alertText = '';
        $alertType = 'danger';//success/warning,danger
        $sync_id = request()->get('sync_id');
        $parcel_name = request()->get('parcel');
        $this->parcel_name = $parcel_name;
        $done = request()->get('done');
        $syncs = [];
        if (!Auth::user()->can('read-logic_sync')) {
            $alertText = 'No permissions to Logic Sync';
            $alertType = 'danger';
            return view('admin.logic_sync', compact('sync_id', 'parcel_name', 'syncs', 'alertText', 'alertType'));
        }
        if ($done) {
            $ldate = date('Y-m-d H:i:s');
            $parcel = Parcel::where('custom_id', $parcel_name)->where("sent", 1)->where('warehouse_id', 2)->whereRaw("(TIME_TO_SEC(TIMEDIFF('" . $ldate . "',created_at))<=30*86400)")->first();
            if ($parcel) {
                $alertText = 'Parcel ' . $parcel_name . ' was sent';
                $alertType = 'danger';
                return view('admin.logic_sync', compact('sync_id', 'parcel_name', 'syncs', 'alertText', 'alertType'));
            }
        }
        $alertText = $this->getSyncs($sync_id);
        /*$sync=new LogicSync();
        $sync->package_name='ASE11111111';
        $sync->bag_name='TR1-11111111';
        $sync->sender='TR1-11111111';
        $sync->package=Package::find(47685);
        $this->syncs[]=$sync;
        $this->bag_syncs[$sync->bag_name]=[];
        $this->bag_syncs[$sync->bag_name][]=$sync;
        $sync=new LogicSync();
        $sync->package_name='ASE11111111';
        $sync->bag_name='TR1-11111111';
        $sync->sender='TR1-11111111';
        $sync->package=Package::find(47670);
        $this->syncs[]=$sync;
        $this->bag_syncs[$sync->bag_name][]=$sync;*/

        $syncs = $this->syncs;

        $this->checkSyncs();
        if ($done) {
            if ($this->ready_sync_count <= 0) {
                $alertText = 'No packages to sync';
                $alertType = 'danger';
            } elseif (empty($parcel_name)) {
                $alertText = 'Empty Parcel name';
                $alertType = 'danger';
            } else {
                $this->doSyncs();
            }
        }
        return view('admin.logic_sync', compact('sync_id', 'parcel_name', 'syncs', 'alertText', 'alertType'));
    }

    public function doSyncs()
    {
        $ldate = date('Y-m-d H:i:s');
        $parcel = Parcel::where('custom_id', $this->parcel_name)->where("sent", 0)->where('warehouse_id', 2)->whereRaw("(TIME_TO_SEC(TIMEDIFF('" . $ldate . "',created_at))<=30*86400)")->first();
        if (!$parcel) {
            $parcel = new Parcel();
            $parcel->custom_id = $this->parcel_name;
            $parcel->warehouse_id = 2;
            $parcel->save();
        }
        foreach ($this->bag_syncs as $bag_name => $syncs) {
            $bag = Bag::where('parcel_id', $parcel->id)->where('custom_id', $bag_name)->first();
            if (!$bag) {
                $bag = new Bag();
                $bag->custom_id = $bag_name;
                $bag->parcel_id = $parcel->id;
                $bag->save();
            }
            foreach ($syncs as $sync) {
                if (!$sync->package)
                    continue;
                if ($sync->status <= 2) {
                    DB::delete("delete from parcel_package where package_id=?", [$sync->package->id]);
                    DB::delete("delete from bag_package where package_id=?", [$sync->package->id]);
                    DB::insert("insert into parcel_package (parcel_id,package_id) values (?,?)", [$parcel->id, $sync->package->id]);
                    DB::insert("insert into bag_package (bag_id,package_id) values (?,?)", [$bag->id, $sync->package->id]);
                    DB::update("update packages set seller_name=? where id=?", [$sync->sender, $sync->package->id]);
                    $sync->status_id = 1;
                    $sync->status = "Done";
                }
            }
        }
    }

    public function checkSyncs()
    {
        $num = 0;
        $this->ready_sync_count = 0;
        foreach ($this->syncs as $sync) {
            $num++;
            $sync->num = $num;
            $sync->status = '';
            $sync->status_id = 0;
            $package = $sync->package;
            if (!$package) {
                $package = Package::whereCustomId($sync->package_name)->first();
                if (!$package) {
                    $sync->status_id = 3;
                    $sync->status = 'NOT FOUND';
                    continue;
                }
                $sync->package = $package;
            }
            if ($package->warehouse_id != 2) {
                $sync->status_id = 3;
                $sync->status = 'Wrong warehouse';
                continue;
            }
            if (!$package->is_ready) {
                $sync->status_id = 3;
                $sync->status = 'NOT READY';
                continue;
            }
            if ($package->parcel && $package->parcel->count()) {
                $sync->status_id = 2;
                $sync->status = 'IN PARCEL';
            }
            if ($package->bag && $package->bag->count()) {
                $sync->status_id = 2;
                if (empty($sync->status))
                    $sync->status = 'IN BAG';
            }
            $this->ready_sync_count++;
        }
    }

    public function getSyncs($sync_id)
    {
        if (empty($sync_id))
            return "";
        $this->syncs = [];
        $this->bag_syncs = [];
        $this->bag_names = [];
        $res = Logic::SyncParcel($sync_id);
        if (!isset($res['status']) || $res['status'] != 200) {
            $err = '';
            if (isset($res['error']))
                $err = "Error:" . $res['error'];
            if (isset($res['cwb']))
                $err = "CWB:" . $res['cwb'];
            return "Error: Parcel sync error " . $err;
        }
        if (!isset($res['response'])) {
            return "Error: Parcel sync no response";
        }
        $res = $res['response'];
        if (!isset($res['result'])) {
            return "Error: Parcel sync no result";
        }
        $res = json_decode($res['result'], true);
        if (!isset($res['ShipmentsShipmentDetail'])) {
            return "Error: Parcel sync no ShipmentsShipmentDetail";
        }
        foreach ($res['bags'] as $bag) {
            list($sym, $num) = explode('-', $bag['code']);
            $bags[$num] = $bag['code'];
            $this->bag_names[] = $bag['code'];
            $this->bag_syncs[$bag['code']] = [];
            //echo $bag['code']."\n";
        }
        foreach ($res['ShipmentsShipmentDetail'] as $ship) {
            //echo $ship['cwb_kod'].' '.$ship['bag_code']."\n";
            if (strpos($ship['cwb_kod'], "ASE") !== 0) continue;
            $sync = new LogicSync();
            $sync->package_name = $ship['cwb_kod'];
            $sync->bag_name = $bags[$ship['bag_code']];
            $sync->sender = $ship['sender'];
            $this->syncs[] = $sync;
            $this->bag_syncs[$sync->bag_name][] = $sync;
        }
        return "";
    }
}
