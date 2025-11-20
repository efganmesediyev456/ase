<?php

namespace App\Http\Controllers\Admin;

use App\Services\Package\PackageService;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Extra\Notification;
use App\Models\Track;
use App\Models\Customer;
use App\Models\Container;
use Auth;

class InCustomsTrackController extends Controller
{
       protected $view = [
           'name' => 'Check Customs Track',
	   'mod_name' => 'in_customs_track',
	   'search' => [
            [
                'name' => 'parcel',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Parcel name'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
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
        'partnerWithLabel' => [
            'label' => 'Partner',
        ],
        'container.name'          => [
            'label' => 'Parcel Name',
        ],
        'tracking_code'          => [
            'label' => 'Tracking #',
        ],
        'weight'          => [
            'label' => 'Weight',
        ],
        'fullname'          => [
            'label' => 'Customer name',
        ],
        'fin'          => [
            'label' => 'Fin code',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.track.statusShortWithLabel',
            ],
            'order' => 'tracks.status',
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

    protected $modelName = 'Track';
    protected $titles = [];
    protected $titles_ok = false;
    protected $parcel_name = '';

    public function __construct()
    {
         parent::__construct();
         \View::share('_list', $this->list);
    }

    public function indexold()
    {
	$alertText='';
	$alertType='danger';//success/warning,danger
	$parcel_name=\request()->get('parcel');
	$this->parcel_name=$parcel_name;
	$ldate = date('Y-m-d H:i:s');
	$set_count=0;
	$unset_count=0;
	if(! Auth::user()->can('update-tracks') )
	{
	   $alertText='No permissions to Update tracks';
	   $alertType='danger';
	} else {
	  $set_tracks=\request()->get('set_tracks');
	  if($set_tracks) {
	    $tracking_codes=preg_split("/[;:,\s]+/",trim($set_tracks));
	    $tracks=Track::whereIn('tracking_code',$tracking_codes)->get();
	    foreach($tracks as $track) {
		$track->in_customs_status=1;
		$track->in_customs_status_at=$ldate;
		$track->bot_comment='Set in customs status';
		$track->worker_comments='SAXLANC';
        $track->status = 18;
		$track->save();
        (new PackageService())->updateStatus($track, 18);
		$set_count++;
	    }
	  }
	  $unset_tracks=\request()->get('unset_tracks');
	  if($unset_tracks) {
	    $tracking_codes=preg_split("/[;:,\s]+/",trim($unset_tracks));
	    $tracks=Track::whereIn('tracking_code',$tracking_codes)->get();
	    foreach($tracks as $track) {
		$track->in_customs_status=0;
		$track->in_customs_status_at=$ldate;
		$track->bot_comment='Unset in customs status';
		$track->worker_comments=NULL;
		$track->save();
		$unset_count++;
	    }
	  }
	}
	$tracks=Track::with(['container'])->where('in_customs_status','>=',1)->orderBy('in_customs_status_at','desc');
	$tracks = $tracks->leftJoin('containers', 'tracks.container_id', 'containers.id');
	$tracks = $tracks->select('tracks.*');
        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $tracks=$tracks->WhereRaw("(tracks.tracking_code LIKE '%" . $q . "%' or tracks.fin LIKE '%" . $q . "%' or tracks.fullname LIKE '%" . $q . "%' or tracks.address LIKE '%" . $q . "%' or tracks.phone LIKE '%" . $q . "%' or tracks.email LIKE '%" . $q . "%' or tracks.detailed_type LIKE '%" . $q . "%')");
        }
        if (\Request::get('parcel') != null) {
            $tracks=$tracks->where('containers.name', \Request::get('parcel'));
        }
	$tracks = $tracks->paginate($this->limit);
        return view('admin.in_customs_tracks', compact('tracks','set_tracks','unset_tracks','set_count','unset_count','alertText','alertType'));
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
                    $tracks = Track::whereIn('tracking_code', $tracking_codes)->get();

                    foreach ($tracks as $track) {
                        $track->in_customs_status = 1;
                        $track->in_customs_status_at = $ldate;
                        $track->bot_comment = 'Set in customs status';
                        $track->worker_comments = strtoupper($note_type);
                        if($note_type!='mutemadi'){
                            $track->status = 18;
                        }
                        $track->save();
                        if($note_type!='mutemadi'){
                            (new PackageService())->updateStatus($track, 18);
                        }
                        $set_count++;
                    }
                    $alertText = $set_count . ' tracks set with note: ' . strtoupper($note_type);
                    $alertType = 'success';
                }
            }

            $unset_tracks = \request()->get('unset_tracks');
            if ($unset_tracks) {
                $tracking_codes = preg_split("/[;:,\s]+/", trim($unset_tracks));
                $tracks = Track::whereIn('tracking_code', $tracking_codes)->get();
                foreach ($tracks as $track) {
                    $track->in_customs_status = 0;
                    $track->in_customs_status_at = $ldate;
                    $track->bot_comment = 'Unset in customs status';
                    $track->worker_comments = NULL;
                    $track->save();
                    $unset_count++;
                }
            }
        }

        $tracks = Track::with(['container'])->where('in_customs_status', '>=', 1)->orderBy('in_customs_status_at', 'desc');
        $tracks = $tracks->leftJoin('containers', 'tracks.container_id', 'containers.id');
        $tracks = $tracks->select('tracks.*');

        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $tracks = $tracks->WhereRaw("(tracks.tracking_code LIKE '%" . $q . "%' or tracks.fin LIKE '%" . $q . "%' or tracks.fullname LIKE '%" . $q . "%' or tracks.address LIKE '%" . $q . "%' or tracks.phone LIKE '%" . $q . "%' or tracks.email LIKE '%" . $q . "%' or tracks.detailed_type LIKE '%" . $q . "%')");
        }

        if (\Request::get('parcel') != null) {
            $tracks = $tracks->where('containers.name', \Request::get('parcel'));
        }

        $tracks = $tracks->paginate($this->limit);
        return view('admin.in_customs_tracks', compact('tracks', 'set_tracks', 'unset_tracks', 'set_count', 'unset_count', 'alertText', 'alertType'));
    }

}
