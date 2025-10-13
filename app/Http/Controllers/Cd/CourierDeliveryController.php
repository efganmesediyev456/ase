<?php

namespace App\Http\Controllers\Cd;

use Alert;
use App\Http\Controllers\Admin\Controller;
use App\Http\Requests;
use App\Models\CD;
use App\Models\Track;
use App\Services\Integration\GfsService;
use App\Services\Package\PackageService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use function auth;
use View;

class CourierDeliveryController extends Controller
{
    protected $view = [
        'name' => 'Courier Delivery',
        'formColumns' => 20,
        'sub_title' => 'Courier Delivery',
        'total_sum' => [
            [
                'key' => 'delivery_price',
                'skip' => 13,
            ],
        ],
	'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
		'default' => '101',
                'optionsFromConfig' => 'ase.attributes.cd.statusCd',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Status',
            ],
            [
                'name' => 'dir',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.cd.direction',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Directions',
            ],
            [
                'name' => 'partner_id',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.partner',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Partners',
            ],
            [
                'name' => 'money_received',
                'type' => 'select_from_array',
                'options' => [0=>'No',1=>'Yes'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Money Received',
            ],
        ],
    ];

    protected $route = 'cd';
    protected $modelName = 'CD';
    protected $extraActions = [
        [
            'key' => 'id',
            //'role'   => '',
            'label' => 'Info',
            'icon' => 'windows2',
            'route' => 'cd.info',
            'color' => 'info',
//            'target' => '_blank',
        ],
    ];

    protected $list = [
        'first_track.partner.name' => [
            'label' => 'Partner',
        ],
        'packages_with_cells_one_br_str' => [
            'label' => 'Packages',
            'type' => 'raw_url',
	    'route' => 'cd.info',
        ],
        'phone' => [
            'label' => 'Phone',
	    'order' => 'phone',
        ],
        'name' => [
            'label' => 'Recipient',
	    'order' => 'name',
        ],
        'address' => [
            'label' => 'Address',
            'type' => 'raw_url',
	    'route' => 'cd.info',
	    'order' => 'address',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'courier_assigned_at' => [
            'label' => 'Time',
	    'order' => 'courier_assigned_at',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'cd.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.cd.statusCd2WithLabel',
            ],
	    'order' => 'status',
        ],
        'not_delivered_status' => [
            'label' => 'Not Delivered Status',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'cd.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.cd.notDeliveredStatusCdWithLabel',
            ],
        ],
        'company_name' => [
            'label' => 'Company',
        ],
        'direction_with_label' => [
            'label' => 'Direction',
        ],
        'invoice_type_with_label' => [
            'label' => 'Invoice',
        ],
        'courier_comment' => [
            'label' => 'Courier Comment',
            'type' => 'editable',
            'editable' => [
                'route' => 'cd.ajax',
                'type' => 'text',
            ],
        ],
        'photo_url2' => [
            'label' => 'Photo',
        ],
        'user_comment' => [
            'label' => 'User Comment',
            'type' => 'text',
        ],
        'delivery_price_with_color' => [
            'label' => 'Delivery Price',
            'type' => 'raw',
        ],
        'paid' => [
            'label' => 'Paid',
            'type' => 'paid',
            'editable' => [
                'route' => 'cd.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabel',
            ],
        ],
        'paid_by' => [
            'label' => 'By',
        ],
        'recieved' => [
            'label' => 'Money Received',
            'type' => 'yes_no',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
        ],
    ];

    public function __construct()
    {
	$this->limit=250;
        parent::__construct();
    }

    public function me()
    {
        return auth()->guard('courier')->user();
    }

    public function destroy($id)
    {
        return;
    }


    public function update(Request $request, $id)
    {
        return;
    }

    public function store(Request $request)
    {
        return;
    }

    /*public function edit($id)
    {
	echo $id;
	return;
    }*/
    public function info($id)
    {
        $item = CD::with(['user'])->find($id);

        if (!$item) {
            abort(404);
        }

        if (($item->status < 4 || $item->status==7) && \Request::get('delivered') == 'yes') {
            $item->status = 4;
            $item->save();
            $item = CD::with(['user'])->find($id);
        }
        if (($item->status < 3 || $item->status==7) && \Request::get('sent') == 'yes') {
            $item->status = 3;
            $item->save();
            $item = CD::with(['user'])->find($id);
        }
        if (\Request::has('not_delivered') && \Request::get('not_delivered')) {
            $item->not_delivered_status = \Request::get('not_delivered');
            $item->save();
            $item = CD::with(['user'])->find($id);
        }

        return view('cd.info', compact('item'));
    }

    public function indexObject()
    {
        $courier = $this->me();
        $validator = Validator::make(\Request::all(), [
            'status ' => 'integer',
            'courier_id ' => 'integer',
            'user_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        $items = CD::with(['tracks','packages','courier'])->select('courier_deliveries.*');
        if (\Request::get('partner_id') != null) {
            $items = $items->leftJoin('tracks', 'courier_deliveries.id', 'tracks.courier_delivery_id');
        }
        if (\request()->get('sort') != null) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            $items = $items->orderBy('courier_deliveries.'.$sortKey, $sortType)->orderBy('courier_deliveries.id', 'desc');
        } else {
            $items = $items->orderBy('courier_deliveries.created_at', 'desc');
        }

	//$arr_status=[6];
	$status=101;
	if(\Request::has('status'))
	    $status=\Request::get('status');
        if ($status != null) {
	   if($status==101)
               $items->whereIn('courier_deliveries.status', [2,3,7]);
	   else
               $items->where('courier_deliveries.status', $status);
	} else {
            $items->whereRaw('(courier_deliveries.status not in (0,1,4,6) or (courier_deliveries.delivery_price > 0 and courier_deliveries.direction in(1,2) and courier_deliveries.recieved=0))');
	  //  $arr_status[]=4;
	}
        //$items->where('courier_deliveries.courier_id', $courier->id)->whereNotIn('courier_deliveries.status', $arr_status)->whereRaw('(courier_deliveries.direction != 3 or courier_deliveries.status != 1)');
        $items->where('courier_deliveries.courier_id', $courier->id)->whereRaw('(courier_deliveries.direction != 3 or courier_deliveries.status != 1)');
        if (\Request::get('q') != null) {
            $items = $items->leftJoin('users', 'courier_deliveries.user_id', 'users.id');
            $q = str_replace('"', '', \Request::get('q'));
            $items->whereRaw("(custom_id like '%" . $q . "%' or packages_txt like '%" . $q . "%' or courier_deliveries.name like '%" . $q . "%' or courier_deliveries.phone like '%" . $q . "%' or user_comment like '%" . $q . "%' or courier_deliveries.address like '%" . $q . "%' or users.customer_id like '%" . $q . "%')");
        }
        if (\Request::get('dir') != null) {
            /*if (\Request::get('dir') == 1) {
                $items->whereIn('direction', [1, 2]);
	    } else {*/
                $items->where('direction', \Request::get('dir'));
            //}
        }
        if (\Request::get('money_received') != null) {
            $items->where('courier_deliveries.recieved', \Request::get('money_received'));
        }
        if (\Request::get('partner_id') != null) {
            $items->where('tracks.partner_id', \Request::get('partner_id'));
        }

        $items_all = $items->get();
        $items = $items->paginate($this->limit);

        View::share('items_all', $items_all);

        return $items;
    }

    public function ajax(Request $request, $id)
    {
        $used = CD::find($id);
        if ($request->get('name') == 'paid') {
            if ($request->get('value') != 0) {
                $type = $request->get('value') == 1 ? 'CASH' : config('ase.attributes.package.paid')[$request->get('value')];
                $request->merge(['value' => 1]);
                //Transaction::addCD($used->id, $type);
            } else {
                //$check = Transaction::where('custom_id', $used->id)->where('paid_for', 'COURIER_DELIVERY')->where('type', 'OUT')->first();
                //if ($check && $check->paid_by != 'PORTMANAT') {
                //    Transaction::where('custom_id', $used->id)->where('paid_for', 'COURIER_DELIVERY')->delete();
                //}
            }
        }
        if ($request->get('name') == 'status') {
            if ($used->status == 6) {
                return;
            }
        }
        if ($request->get('name') == 'courier_id') {
            if ($request->get('value') != 0) {
                if (!$used->status) {
                    $used->status = 2;
                    $used->save();
                }
            }
        }

        if($request->get('name') == 'status' && ($request->get('value') == 2 || $request->get('value') == 4)){
            $tracks = explode(',', $used->packages_txt);
            foreach ($tracks as $trackCode) {
                $track = Track::query()->where('tracking_code', $trackCode)->first();
                if ($track) {
                    $service = new PackageService();
                    $service->updateStatus($track, $request->get('value') == 2 ? 21 : 17);

                    $track->status = $request->get('value') == 2 ? 21 : 17;
                    $track->comment_txt = "Track status updated to: " . $request->get('value') == 2 ? 21 : 17;
                    $track->save();
                }
            }
        }

        //if ($request->get('name') == 'status' && array_key_exists($request->get('value'), GfsService::COURIER_STATES)) {
        //    $tracks = explode(',', $used->packages_txt);
	//
        //    foreach ($tracks as $trackCode) {
        //        $track = Track::query()->where('tracking_code', $trackCode)->first();
        //        if ($track) {
        //            $service = new PackageService();
        //            $service->updateStatus($track, $request->get('value'));
	//
        //            $track->status = 22;
        //            $track->comment_txt = "Track status updated to: " . $request->get('value');
        //            $track->save();
	//
        //            return Response::json(['message' => "Item's " . $request->get('name') . " has been updated!!"]);
        //        }
        //    }
        //}

        return parent::ajax($request, $id);
    }

}
