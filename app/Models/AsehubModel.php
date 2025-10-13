<?php

namespace App\Models;

use \stdClass;
use Illuminate\Database\Eloquent\Model;

class AsehubModel extends Model
{
    public $doLog=true;
    public $doErr=false;
    public $message;
    public $code;
    public $track=NULL;
    public $customer=NULL;

    function log($message) {
	if($this->doLog)
	   echo $message."\n";    
	//file_put_contents("/var/log/ase_hub.log",$message."\n",FILE_APPEND);
    }

    function err($message) {
	if($this->doErr) {
	    $ldate = date('Y-m-d H:i:s');
	    file_put_contents("/var/log/ase_asehub_error.log",$ldate." model ".$message."\n",FILE_APPEND);
	}
    }

    function track_add_from_json($json_txt) {
	if(empty($json_txt)) {
	    $this->message="Empty body";
	    return false;
	}
        $json = json_decode($json_txt,false);
	if(!$json) {
	    $this->message="Wrong json";
	    return false;
	}

	//tracking code
	if(!isset($json->parcelNumber) || empty($json->parcelNumber)) {
	    $this->message="No parcelNumber found";
	    return false;
	}
	$track = Track::where('tracking_code',$json->parcelNumber)->whereNull('deleted_at')->first();
	if($track) {
	    $this->message="Track with parcelNumber ".$json->parcelNumber." already exists";
	    return false;
	}
	$track=new Track();
	$track->tracking_code=$json->parcelNumber;
	//-------
	
	$track->status=1;//In warehouse status
	
	//weight
	if(!isset($json->weight) || empty($json->weight)) {
	    $this->message="No weight found";
	    return false;
	}
	$track->weight=$json->weight;
	//-------
	
	//delivery price
	if(!isset($json->deliveryCost) || empty($json->deliveryCost)) {
	    $this->message="No deliveryCost found";
	    return false;
	}
	$track->delivery_price=$json->deliveryCost;
	//-------
	
	//--goods items
	$detailed_type='';
	$items_number=0;
	if(isset($json->items) && is_array($json->items)) {
	   foreach($json->items as $item) {
	       if($detailed_type) $detailed_type.=' ; ';  
	       if(isset($item->descriptionEN)) {
	           $detailed_type.=$item->descriptionEN;
	       } else if(isset($item->description)) {
	           $detailed_type.=$item->description;
	       }
	       if(isset($item->quantity)) {
	           $item_number=$item->quantity;
		   $detailed_type.=' x '.$item_number;
		   $items_number+=$item_number;
	       }
	   }
	}
	$track->detailed_type=$detailed_type;
	$track->number_items=$items_number;
	//--------
	
	//Invoice price
	if(!isset($json->totalValueUSD) || empty($json->totalValueUSD)) {
	    $this->message="No totalValueUSD found";
	    return false;
	}
	$track->shipping_amount=$json->totalValueUSD;
	//-----
	
	//Recipient begin
	if(!isset($json->recipient)) {
	    $this->message="No Recipient found";
	    return false;
	}
	$rec=$json->recipient;
	//Recipient name
	if(!isset($rec->name) || empty($rec->name)) {
	    $this->message="No Recipient Name found";
	    return false;
	}
	$track->fullname=$rec->name;
	//----
	if(isset($rec->email) || !empty($rec->email)) 
	    $track->email=$rec->email;
	if(isset($rec->phone) || !empty($rec->phone)) 
	    $track->phone=$rec->phone;
	if(isset($rec->companyName) || !empty($rec->companyName)) 
	    $track->company_name=$rec->company_name;
	if(isset($rec->zipCode) || !empty($rec->zipCode)) 
	    $track->zip_code=$rec->zipCode;
	if(isset($rec->region1) || !empty($rec->region1)) 
	    $track->region_name=$rec->region1;
	if(isset($rec->city) || !empty($rec->city)) 
	    $track->city_name=$rec->city;
	if(isset($rec->street) || !empty($rec->street)) 
	    $track->address=$rec->street;
	if(isset($rec->buildingNumber) || !empty($rec->buildingNumber)) 
	    $track->address.=' '.$rec->buildingNumber;
	$track->parseCity();
	$track->assignCustomer();
	//Recipient end
	$track->save();
	$this->track=$track;
	$this->customer=$track->customer;
	$this->message="Successfully added";
	return true;
    }

}

