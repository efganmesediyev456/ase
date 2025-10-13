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

class TracksImportIHBController extends Controller
{
       protected $view = [
           'name' => 'Import iHerb Tracks',
	   'mod_name' => 'tracks_import_ihb',
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
	    $container=Container::where('name',$parcel_name)->where('partner_id',1)->first();
	    if(!$container) {
	        $container=new Container();
		$container->name=$parcel_name;
		$container->partner_id=1;
		$container->save();
	    }
	    $container_id=$container->id;
	}
	if (\request()->hasFile('import_excel')) {
	    $fileName = \request()->file('import_excel')->getRealPath();
	    $Reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
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
		case 'Parcel No.' :
		    $this->titles['tracking_code']=$cnt;
		    $titles_cnt++;
		    break;
		case 'Total Parcel Weight, kg brutto' :
		    $this->titles['total_weight']=$cnt;
		    $titles_cnt++;
		    break;
		case 'LM number' :
		    $this->titles['lm_number']=$cnt;
		    break;
		case 'Box ID' :
		    $this->titles['box_id']=$cnt;
		    break;
		case 'Country' :
		    $this->titles['country']=$cnt;
		    break;
		case "Addressee's":
		    $this->titles['full_name']=$cnt;
		    $titles_cnt++;
		    break;
		case 'Region' :
		    $this->titles['region']=$cnt;
		    break;
		case 'City' :
		    $this->titles['city']=$cnt;
		    break;
		case 'Full Street' :
		    $this->titles['street']=$cnt;
		    $titles_cnt++;
		    break;
		case 'Flat' :
		    $this->titles['flat']=$cnt;
		    break;
		case 'Building' :
		    $this->titles['Building']=$cnt;
		    break;
		case 'Phone' :
		    $this->titles['phone']=$cnt;
		    $titles_cnt++;
		    break;
		case 'Curency' :
		case 'Currency' :
		    $this->titles['currency']=$cnt;
		    break;
		case 'Postal' :
		    $this->titles['zip_code']=$cnt;
		    break;
		case 'Value' :
		    $this->titles['value']=$cnt;
		    break;
		case 'Order Date' :
		    $this->titles['order_date']=$cnt;
		    break;
		case 'ItemsDescrRU' :
		    $this->titles['items_descr_ru']=$cnt;
		    break;
		case 'ItemsDescrEN' :
		    $this->titles['items_descr_en']=$cnt;
		    $titles_cnt++;
		    break;
		case 'Items Quantity' :
		    $this->titles['items_quantity']=$cnt;
		    $titles_cnt++;
		    break;
		case 'ItemsWeight' :
		    $this->titles['items_weight']=$cnt;
		    break;
		case 'ItemsPrice' :
		    $this->titles['items_price']=$cnt;
		    break;
		case 'ItemsCountry of production' :
		    $this->titles['items_country']=$cnt;
		    break;
		case 'Customs Code' :
		    $this->titles['customs_code']=$cnt;
		    break;
		case 'Product Code' :
		    $this->titles['product_code']=$cnt;
		    break;
		case 'More then 150' :
		    $this->titles['more_than_150']=$cnt;
		    break;
	    }
	    $cnt++;
	}
	if($titles_cnt>=6) $this->titles_ok=true;
    }

    public function loadTrackFromArray($arr,$container_id) {
        if(!$this->titles_ok) {
	   $this->assignTitles($arr);
	   return NULL;
	}
	if(strpos($arr[$this->titles['tracking_code']],'IHB') !== 0) return NULL;
	$track=Track::where('tracking_code',$arr[$this->titles['tracking_code']])->where('partner_id',1)->first();
	$track_new=false;
	if(!$track) {
	    $track=new Track();
	    $track_new=true;
	}
	$track->partner_id=1;//iherb
	$track->warehouse_id=19;//iherb
	$track->container_id=$container_id;
	$track->tracking_code=$arr[$this->titles['tracking_code']];
	$track->second_tracking_code=$arr[$this->titles['lm_number']];
	$track->weight=$arr[$this->titles['total_weight']];
	$track->fullname=$arr[$this->titles['full_name']];
        if(array_key_exists('zip_code',$this->titles)) {
	   $str=$arr[$this->titles['zip_code']];
	   $str=str_replace(',','',$str);
	   $str=str_replace('.00','',$str);
	   $track->zip_code=$str;
	}
	$track->region_name=$arr[$this->titles['region']];
	if(array_key_exists('city',$this->titles))
	    $track->city_name=$arr[$this->titles['city']];
	$track->address=$arr[$this->titles['street']];
	if(array_key_exists('building',$this->titles))
	    if(!empty($arr[$this->titles['building']])) $track->address.=' '.$arr[$this->titles['building']];
	if(array_key_exists('flat',$this->titles))
	    if(!empty($arr[$this->titles['flat']])) $track->address.=' '.$arr[$this->titles['flat']];
	$str=$arr[$this->titles['phone']];
	$str=str_replace(',','',$str);
	$str=str_replace('.00','',$str);
	$track->phone=$str;
	$track->currency=$arr[$this->titles['currency']];
	$track->shipping_amount=$arr[$this->titles['value']];
	if(!empty($arr[$this->titles['items_descr_en']])) {
	    $track->detailed_type=$arr[$this->titles['items_descr_en']];
	} else if(!empty($arr[$titles['items_descr_ru']])) {
	    $track->detailed_type=$arr[$this->titles['items_descr_ru']];
	}
	$track->number_items=$arr[$this->titles['items_quantity']];
	if($track_new || !$track->status) {
	   $track->status=1;
	}

	$track->parseCity();
	$track->assignCustomer();
	$track->save();
	if($track_new) {
        Notification::sendTrack($track->id, $track->status);
        Notification::sendTrack($track->id, 'IHERB_RUS_SMART');
    }
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
