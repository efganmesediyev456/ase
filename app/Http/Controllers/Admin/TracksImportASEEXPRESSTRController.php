<?php

namespace App\Http\Controllers\Admin;

use DB;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\Extra\Notification;
use App\Models\Track;
use App\Models\Customer;
use App\Models\Container;
use Auth;

class TracksImportASEEXPRESSTRController extends Controller
{
       protected $view = [
           'name' => 'Import ASE Express TR Tracks',
	   'mod_name' => 'tracks_import_aseexpresstr',
       ];
       protected $list = [
        'num'          => [
            'label' => '#',
        ],
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
        'zip_code'          => [
            'label' => 'Zip code',
        ],
	'city.name' => [
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
	'detailed_type' => [
            'label' => 'Items Description',
        ],
	'number_items' => [
            'label' => 'Items Quantity',
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
    public function import()
    {
	$alertText='';
	$alertType='danger';//success/warning,danger
	$parcel_name=\request()->get('parcel');
	$container=NULL;
	$container_id=NULL;
	if(!empty($parcel_name)) {
	    $container=Container::where('name',$parcel_name)->where('partner_id',6)->first();
	    if(!$container) {
	        $container=new Container();
		$container->name=$parcel_name;
		$container->partner_id=6;
		$container->save();
	    }
	    $container_id=$container->id;
	}
	if (\request()->hasFile('import_excel')) {
	    $fileName = \request()->file('import_excel')->getRealPath();
	    $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
	    $spreadSheet = $Reader->load($fileName);
	    $excelSheet = $spreadSheet->getActiveSheet();
	    $spreadSheetAry = $excelSheet->toArray();
	    //print_r($spreadSheetAry);
	    $tracks=[];
	    foreach($spreadSheetAry as $arr) {
		 $track=$this->loadTrackFromArray($arr,$container_id);
		 if(!$track) continue;
		 $tracks[]=$track;
	    }
	}
        return view('admin.tracks_import', compact('parcel_name','tracks','alertText','alertType'));
    }

    public function assignTitles($arr) {
	$cnt=0;
	$titles_cnt=0;
	foreach($arr as $title) {
	    switch($title) {
		case 'TR NUMBER' :
		    $this->titles['tracking_code']=$cnt;
		    $titles_cnt++;
		    break;
		case 'QUANTITY OF GOODS' :
		    $this->titles['goods_quantity']=$cnt;
		    $titles_cnt++;
		    break;
		case 'DIRECTION' :
		    $this->titles['direction']=$cnt;
		    break;
		case 'WEIGHT' :
		case 'WEIGHT ' :
		    $this->titles['weight']=$cnt;
		    break;
		case 'INVOICE PRICE' :
		    $this->titles['invoice']=$cnt;
		    break;
		case "NAME OF GOODS":
		    $this->titles['goods_name']=$cnt;
		    $titles_cnt++;
		    break;
		case 'IDXAL NAME' :
		    $this->titles['full_name']=$cnt;
		    $titles_cnt++;
		    break;
		case 'IDXAL ADRES' :
		    $this->titles['street']=$cnt;
		    $titles_cnt++;
		    break;
		case 'CURRENCY TYPE' :
		    $this->titles['currency']=$cnt;
		    $titles_cnt++;
		    break;
	    }
	    $cnt++;
	}
	if($titles_cnt>=5) $this->titles_ok=true;
    }

    public function loadTrackFromArray($arr,$container_id) {
        if(!$this->titles_ok) {
	   $this->assignTitles($arr);
	   return NULL;
	}
	if(strpos($arr[$this->titles['tracking_code']],'ASE') === 0) return NULL;
	if(empty($arr[$this->titles['tracking_code']])) return NULL;
	$track=Track::where('tracking_code',$arr[$this->titles['tracking_code']])->where('partner_id',6)->first();
	$track_new=false;
	if(!$track) {
	    $track=new Track();
	    $track_new=true;
	}
	$track->partner_id=6;// Ase Express TR
	$track->container_id=$container_id;
	$track->tracking_code=$arr[$this->titles['tracking_code']];
	$track->weight=$arr[$this->titles['weight']];
	$track->fullname=$arr[$this->titles['full_name']];
	$track->address=$arr[$this->titles['street']];
	//$track->phone=$arr[$this->titles['phone']];
	$track->shipping_amount=$arr[$this->titles['invoice']];
	$track->detailed_type=$arr[$this->titles['goods_name']];
	$track->number_items=$arr[$this->titles['goods_quantity']];
	$track->currency=$arr[$this->titles['currency']];
	if($track_new || !$track->status) {
	   $track->status=1;
	}

	$track->parseCity();
	$track->assignCustomer();
	$track->save();
	//if($track_new)
        //    Notification::sendTrack($track->id,  $track->status);

	return $track;
    }

    public function index()
    {
	$alertText='';
	$alertType='danger';//success/warning,danger
	$parcel_name=\request()->get('parcel');
	$this->parcel_name=$parcel_name;
	$tracks=[];
	if(! Auth::user()->can('create-tracks') )
	{
	   $alertText='No permissions to Create tracks';
	   $alertType='danger';
	   return view('admin.tracks_import', compact('parcel_name','tracks','alertText','alertType'));
	}
        return view('admin.tracks_import', compact('parcel_name','tracks','alertText','alertType'));
    }

}
