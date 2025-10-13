<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Courier;
use App\Models\CD;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CourierTrackController extends Controller
{
    protected $can = [
        'export' => false,
    ];
    protected $view = [
        'name' => 'Courier Track',
        'formColumns' => 10,
        'sub_title' => 'Courier Track Scan',
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
                'type' => 'select2',
                'name' => 'courier_id',
                'attribute' => 'name',
                'model' => 'App\Models\Courier',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All couriers',
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
        ],
    ];


    protected $route = 'courier_tracks';
    protected $modelName = 'Track';

    protected $list = [
        'partner_with_label' => [
            'label' => 'Partner',
        ],
        'courier_delivery.courier.name' => [
            'label' => 'Courier',
        ],
       'tracking_code' => [
            'label' => 'Tracking #',
        ],
        'fullname' => [
            'label' => 'Customer name',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'status_with_label' => [
            'label' => 'Status',
        ],
        'courier_delivery' => [
            'type' => 'cd_status',
            'label' => 'CD Status',
        ],
        'city_name' => [
            'label' => 'City',
        ],
        'region_name' => [
            'label' => 'Region',
        ],
        'address' => [
            'label' => 'Address',
            'type' => 'editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'text',
            ],
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'order' => 'created_at',
        ],
    ];

    protected $fields = [
    ];

    public function __construct()
    {
        $cId = 0;//request()->route('courier_id');
	$arr=request()->query();
	if(count($arr)>=1 && array_keys($arr)[0] && !array_values($arr)[0]) 
	   $cId=array_keys($arr)[0];
	if($cId) {
            $courier = Courier::find($cId);
            if ($courier) {
              $this->routeParams = [
                'courier_id' => $courier->id,
              ];
              $this->view['name'] = 'Courier tracks scaned';
	      //$this->fields[0]['default']=$courier->id;
            }
	}
        parent::__construct();
    }

    public function barcodeScan($code)
    {
	 $courier_id=NULL;
	 if (request()->has('courier_id') && request()->get('courier_id') )
              $courier_id = request()->get('courier_id');
	 if(!$courier_id) {
             return response()->json([
               'error' => 'No Courier selected',
             ]);
	 }
         $courier = Courier::find($courier_id);
	 if(!$courier) {
             return response()->json([
               'error' => 'Wrong Courier id: '.$courier_id,
             ]);
	 }
	 if(!$code) {
             return response()->json([
               'error' => 'No tracking code',
             ]);
	 }
	 $track=Track::where('tracking_code',$code)->first();
	 if(!$track) {
             return response()->json([
               'error' => 'No track found with Tracking code: '.$code,
             ]);
	 }

            $cd_status = 1; //accepted
            $cd = $track->courier_delivery;
            if ($cd) {
                $cd_status = $cd->status;
            }
            $str = $track->worker_comments;
            if ($cd && (($cd->courier_id != $courier_id) || ($cd->address != $track->address))) {
                $cd = CD::updateTrack($cd, $track, $courier_id);
            }
            /*if (!$cd && $track->status != 18) {
                $cd = CD::leftJoin('tracks', 'courier_deliveries.id', '=', 'tracks.courier_delivery_id')->select('courier_deliveries.*');
                //$cd->where('courier_deliveries.status',1)->where('courier_id',$courier_id)->first();
                $cd = $cd->where('courier_deliveries.courier_id', $courier_id);
                $cd = $cd->where('courier_deliveries.status', $cd_status);
                $cd = $cd->where('tracks.container_id', $track->container_id)->where('tracks.customer_id', $track->customer_id)->where('tracks.status', $track->status)->first();
                if ($cd) {
                    if ($cd->address != $track->address) {
                        $cd = null;
                    } else {
                        $cd->packages_txt .= ',' . $track->tracking_code;
                    }
                }
	    }*/ //Commented One track one cd
            $new_cd = false;
            if (!$cd) {
                $new_cd = true;
                $cd = CD::newCD($track, $courier_id, $cd_status);
            }
            $cd->save();
            $track->courier_delivery_id = $cd->id;
	    $track->bot_comment="Track Courier Scan";
            //if($new_cd)
            //    $track->status=21;
            $track->save();


         return response()->json([
            'add_track' => true,
            'track' => $track,
            'html' => view('admin.widgets.single-package')->with([
		'extraActions' => $this->extraActions,
		'item' => $track,
		'_list' => $this->list,
                '_view' => $this->view,
            ])->render(),
         ]);
    }

    public function index()
    {
	$courier=NULL;
        if (\Request::get('courier_id') != null) {
           $courier = Courier::find(\Request::get('courier_id'));
        }
        if (\Request::get('cwb') != null) {
        }
    	$search=[
                'type' => 'select2',
                'name' => 'courier_id',
                'attribute' => 'name',
                'model' => 'App\Models\Courier',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All couriers',
        ];
        return view('admin.courier_track', compact('courier', 'search', 'alertText', 'alertType'));
    }

    /*public function indexObject()
    {

        $items = Track::with(['carrier', 'partner', 'container', 'airbox', 'city', 'courier_delivery']);//::paginate($this->limit);
	$items = $items->leftJoin('courier_deliveries', 'tracks.courier_delivery_id', 'courier_deliveries.id');
	$items = $items->select('tracks.*');
        if (\request()->get('sort') != null) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            $items = $items->orderBy($sortKey, $sortType)->orderBy('tracks.id', 'desc');
        } else {
            $items = $items->orderBy('tracks.created_at', 'desc')->orderBy('tracks.id', 'desc');
        }
        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where(function ($query) use ($q) {
                $query->orWhere("tracks.tracking_code", "LIKE", "%" . $q . "%")->orWhere("tracks.fullname", "LIKE", "%" . $q . "%")->orWhere("tracks.address", "LIKE", "%" . $q . "%")->orWhere("tracks.phone", "LIKE", "%" . $q . "%")->orWhere("tracks.email", "LIKE", "%" . $q . "%")->orWhere("tracks.detailed_type", "LIKE", "%" . $q . "%");
            });
        }
        if(array_key_exists('courier_id',$this->routeParams)) {
           $items = $items->where('courier_deliveris.courier_id', $this->routeParams['courier_id']);
        }
        if (\Request::get('partner_id') != null) {
           $items = $items->where('tracks.partner_id', \Request::get('partner_id'));
        }
        if (\Request::get('courier_id') != null) {
           $items = $items->where('courier_deliveries.courier_id', \Request::get('courier_id'));
        }
        $items = $items->paginate($this->limit);
        return $items;
    }*/

}
