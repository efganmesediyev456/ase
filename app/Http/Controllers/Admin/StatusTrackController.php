<?php

namespace App\Http\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Track;
use App\Models\CD;
use App\Models\Customer;
use App\Models\Container;
use Carbon\Carbon;
use Auth;
use App\Services\Package\PackageService;
use App\Models\Extra\Notification;
use App\Events\TrackCell;

class StatusTrackController extends Controller
{
       protected $view = [
           'name' => 'Status Track',
	   'mod_name' => 'status_track',
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
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.statusShort',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Status',
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
        'bot_comment' => [
            'label' => 'Bot Comment',
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
        'updated_at' => [
            'label' => 'UpdatedAt',
            'order' => 'updated_at',
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

    public function index()
    {
	$alertText='';
	$alertType='danger';//success/warning,danger
	$parcel_name=\request()->get('parcel');
	$this->parcel_name=$parcel_name;
	$ldate = date('Y-m-d H:i:s');
	$set_count=0;
	$unset_count=0;
	$tracks=NULL;
	if(! Auth::user()->can('update-tracks') )
	{
	   $alertText='No permissions to Update tracks';
	   $alertType='danger';
	} else {
	  $set_tracks=\request()->get('set_tracks');
	  $clear_parcel=\request()->get('clear_parcel');
	  $scan_no_check=\request()->get('scan_no_check');
	  $ignore_list=\request()->get('ignore_list');
	  $status=\request()->get('set_status');
	  if($set_tracks) {
	    $tracking_codes=preg_split("/[;:,\s]+/",trim($set_tracks));
	    $tracks=Track::whereIn('tracking_code',$tracking_codes)->get();
	    foreach($tracks as $track) {
		$id=$track->id;
		$track_changed=false;
	        $bot_comment='';
		if ($status && ($track->status != $status)) {
                    if ($status == 16 || $status == 20) {
                        $track->notification_inbaku_at = Carbon::now();
                        //$track->save();
                    }
                    if ($track->partner_id != 5 && $track->partner_id != 6) {
                        if($status==16) { // In Baku
                            $isPudo=false;
                            if($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id || $track->yenipoct_office_id  || $track->kargomat_office_id)
                                $isPudo=true;
                            if(!in_array($track->partner_id,[8]) || $isPudo) { //If GFS then must be PUDO
                                Notification::sendTrack($id, $status);
                            }
                        } elseif($status==20) {
                            if(!in_array($track->partner_id,[9]) || !$track->paid) { //If TAOBAO then must not be PAID
                                Notification::sendTrack($id, $status);
                            }
                        } else {
                            Notification::sendTrack($id, $status);
                        }
                    }
		    $bot_comment.='Set status';
		    $track->status=$status;
	            if ($status == 17) { //if done
        	        $cd = $track->courier_delivery;
                	if ($cd && ($cd->status != 6)) { // delete courier delivery if not done
                    	    $cd = CD::removeTrack($cd, $track);
                	}
                	event(new TrackCell('done', $track->id));
            	    }
            	    if ($status == 18) { //if in customs
                	$cd = $track->courier_delivery;
                	if ($cd && ($cd->status != 6)) { // delete courier delivery if not done
                    	$cd = CD::popTrack($cd, $track);
                	}
            	    }
            	    if($status == 16){
                	(new PackageService())->updateStatus($track, 24);
            	    }
            	    (new PackageService())->updateStatus($track, $status);
		    $track_changed=true;
		}
		if($clear_parcel) {
		    $bot_comment.=' +Clear parcel&bag';
		    if($track->container)
		        $bot_comment.=' container='.$track->container->name;
		    if($track->airbox)
		        $bot_comment.=' airbox='.$track->airbox->name;
		    $track->container_id=NULL;
		    $track->airbox_id=NULL;
		    $track_changed=true;
		}
		if($scan_no_check) {
		    if($scan_no_check==1) {
			  $track->scan_no_check=true;
			  $bot_comment.=" +Don't check customs when scaning";
		          $track_changed=true;
		   } else if($scan_no_check==2) {
			  $track->scan_no_check=false;
			  $bot_comment.=" +Check customs when scaning";
		          $track_changed=true;
		    }
	        }
		if($ignore_list) {
			  $track->scan_no_check=true;
			  $bot_comment.=" +Ignore";
			  DB::delete("delete from tracks_ignore_list where tracking_code=?", [$track->tracking_code]);
			  DB::insert("insert into tracks_ignore_list (tracking_code) values (?)", [$track->tracking_code]);
		          $track_changed=true;
	        }
	    	if($track_changed) {
		    $track->bot_comment=$bot_comment;
		    $track->save();
		    $set_count++;
                }
	    }
	    $tracks=Track::with(['container'])->orderBy('updated_at','desc');
	    $tracks = $tracks->leftJoin('containers', 'tracks.container_id', 'containers.id');
	    $tracks = $tracks->select('tracks.*');
	    $tracks = $tracks->whereIn('tracking_code', $tracking_codes);
	    $tracks = $tracks->paginate($this->limit);
	  }
	}
        return view('admin.status_tracks', compact('tracks','set_tracks','set_count','alertText','alertType'));
    }

}
