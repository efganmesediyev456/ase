<?php

namespace App\Http\Controllers\Admin;

use App\Models\Package;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Extra\Notification;
use App\Models\Track;
use App\Models\Customer;
use App\Models\Container;
use Auth;

class InCustomsPackageController extends Controller
{
       protected $view = [
           'name' => 'Check Customs Package',
	   'mod_name' => 'in_customs_package',
	   'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
	   ],
       ];
       protected $list = [
        /*'num'          => [
            'label' => '#',
    ],*/
        'tracking_code'          => [
            'label' => 'Tracking #',
        ],
        'weight'          => [
            'label' => 'Weight',
        ],
        'user.fullname'          => [
            'label' => 'Customer name',
        ],
        'user.fin'          => [
            'label' => 'Fin code',
        ],
        'user.phone' => [
            'label' => 'Phone',
        ],
        'status' => [
               'label' => 'Status',
               //'type' => 'select-editable',
               'type' => 'package_status',
               'editable' => [
                   'route' => 'packages.ajax',
                   'type' => 'select',
                   'sourceFromConfig' => 'ase.attributes.package.statusWithLabel',
               ],
        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
            'order' => 'package_carriers.status',
        ],
/*	'city_name' => [
            'label' => 'City',
        ],
	'region_name' => [
            'label' => 'Region',
        ],
	'address' => [
            'label' => 'Address',
        ],
	'phone' => [
            'label' => 'Phone',
        ],
        'currency' => [
            'label' => 'Currency',
        ],
        'shipping_amount' => [
            'label' => 'Invoice price',
        ],
        'delivery_price_with_label' => [
            'label' => 'Delivery price',
        ],
	'detailed_type' => [
            'label' => 'Items Description',
        ],
	'number_items' => [
            'label' => 'Items Quantity',
    ],*/
        'in_customs_status_at' => [
            'label' => 'Check Customs At',
            'order' => 'in_customs_status_at',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'order' => 'created_at',
        ],
    ];

    protected $modelName = 'Package';
    protected $titles = [];
    protected $titles_ok = false;
    protected $parcel_name = '';

    public function __construct()
    {
         parent::__construct();
         \View::share('_list', $this->list);
    }


    public function index()
    {
        $alertText = '';
        $alertType = 'danger';
        $parcel_name = \request()->get('parcel');
        $this->parcel_name = $parcel_name;
        $ldate = date('Y-m-d H:i:s');
        $set_count = 0;
        $unset_count = 0;

        if (!Auth::user()->can('update-tracks')) {
            $alertText = 'No permissions to Update tracks';
            $alertType = 'danger';
        } else {

            $set_tracks = \request()->get('set_tracks');
            $note_type = \request()->get('note_type');

            if ($set_tracks) {
                if (!$note_type) {
                    $alertText = 'Please select a note type (Smart, Say, Mutemadi, or Price)';
                    $alertType = 'danger';
                } else {
                    $tracking_codes = preg_split("/[;:,\s]+/", trim($set_tracks));
                    $packages = Package::whereIn('tracking_code', $tracking_codes)->get();

                    foreach ($packages as $package) {
                        $package->in_customs_status = 1;
                        $package->in_customs_status_at = $ldate;
                        $package->bot_comment = 'Set in customs status';
                        $package->worker_comments = strtoupper($note_type);
//                        if($note_type!='mutemadi'){
                            $package->status = 4;
                            Notification::sendTrack($package->id, 4);
//                        }
                        $package->save();
//                        if($note_type!='mutemadi'){
//                            (new PackageService())->updateStatus($package, 4);
//                        }
                        $set_count++;
                    }
                    $alertText = $set_count . ' tracks set with note: ' . strtoupper($note_type);
                    $alertType = 'success';
                }
            }

            $unset_tracks = \request()->get('unset_tracks');
            if ($unset_tracks) {
                $tracking_codes = preg_split("/[;:,\s]+/", trim($unset_tracks));
                $packages = Package::whereIn('tracking_code', $tracking_codes)->get();
                foreach ($packages as $package) {
                    $package->in_customs_status = 0;
                    $package->in_customs_status_at = $ldate;
                    $package->bot_comment = 'Unset in customs status';
                    $package->worker_comments = NULL;
                    $package->save();
                    $unset_count++;
                }
            }
        }

        $packages = Package::where('in_customs_status', '>=', 1)->orderBy('in_customs_status_at', 'desc');
        $packages = $packages->select('packages.*');



        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $packages->where(function ($query) use ($q) {
                $query->where('tracking_code', 'like', "%$q%")
                    ->orWhere('detailed_type', 'like', "%$q%");

                $query->orWhereHas('user', function ($u) use ($q) {
                    $u->where('name', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhere('fin', 'like', "%$q%")
                        ->orWhere('email', 'like', "%$q%")
                        ->orWhereRaw("CONCAT(name, ' ', surname) LIKE ?", ["%$q%"])
                        ->orWhere('address', 'like', "%$q%")
                        ->orWhere('phone', 'like', "%$q%");
                });
            });
        }

        $packages = $packages->paginate($this->limit);
        return view('admin.in_customs_packages', compact('packages', 'set_tracks', 'unset_tracks', 'set_count', 'unset_count', 'alertText', 'alertType'));
    }

}
