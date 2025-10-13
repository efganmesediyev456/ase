<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use App\Traits\ModelEventLogger;

class CA extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'courier_areas';
    public $dates = ['deleted_at'];


    public function getPartnerWithLabelAttribute()
    {
        $partner_id = $this->attributes['partner_id'] ?? null;
        if (!$partner_id) {
            return '';
        }

        $partners = config('ase.attributes.track.partner');

        return $partners[$partner_id] ?? '';
    }

    public static function areaCourier($track)
    {
	 $couriers=Courier::orderBy('name','asc')->get();
	 $track_area=['CITY'=>$track->city_name,'REGION'=>$track->region_name,'ADDRESS'=>$track->address];
	 $c_areas=[]; // Courier area types that maches track area
	 foreach($couriers as $courier) { //set c_areas and unset_c_ids
	     $c_areas[$courier->id]=['CITY'=>0,'REGION'=>0,'ADDRESS'=>0];
	     foreach($courier->areas as $area) {
		 if($area->partner_id && $area->partner_id != $track->partner_id) 
		     continue;
		 if($area->mach == 'NOT_EQUAL') {
		     if(strtolower(a2l($area->name)) != strtolower(a2l($track_area[$area->type]))) { // equal
			 $c_areas[$courier->id][$area->type]=2;
		     }
		 } else if($area->mach == 'NOT_IN') {
		     if(stripos(a2l($track_area[$area->type]),a2l($area->name)) === false) { // found
			 $c_areas[$courier->id][$area->type]=2;
		     }
	         } else if($area->mach == 'EQUAL') {
		     if(strtolower(a2l($area->name)) == strtolower(a2l($track_area[$area->type]))) { // equal
			 $c_areas[$courier->id][$area->type]=1;
		     }
		 } else if($area->mach == 'IN') {
		     if(stripos(a2l($track_area[$area->type]),a2l($area->name)) !== false) { // found
			 $c_areas[$courier->id][$area->type]=1;
		     }
		 }
	     }
	 }
	 foreach($couriers as $courier) { //Find city and region with address 
	     if($c_areas[$courier->id]['CITY']==1 && $c_areas[$courier->id]['REGION']==1 && $c_areas[$courier->id]['ADDRESS']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find city or region with address
	     if(($c_areas[$courier->id]['CITY']==1 || $c_areas[$courier->id]['REGION']==1) && $c_areas[$courier->id]['ADDRESS']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find city and region with not address
	     if($c_areas[$courier->id]['CITY']==1 && $c_areas[$courier->id]['REGION']==1 && $c_areas[$courier->id]['ADDRESS']==2) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find find city or region with not address
	     if(($c_areas[$courier->id]['CITY']==1 || $c_areas[$courier->id]['REGION']==1) && $c_areas[$courier->id]['ADDRESS']==2) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find city and region
	     if($c_areas[$courier->id]['CITY']==1 && $c_areas[$courier->id]['REGION']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find city or region
	     if($c_areas[$courier->id]['CITY']==1 || $c_areas[$courier->id]['REGION']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not city and region  with address
	     if(($c_areas[$courier->id]['CITY']==2 && $c_areas[$courier->id]['REGION']==2) && $c_areas[$courier->id]['ADDRESS']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not city or region with address
	     if(($c_areas[$courier->id]['CITY']==2 || $c_areas[$courier->id]['REGION']==2) && $c_areas[$courier->id]['ADDRESS']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not city and region  with not address
	     if(($c_areas[$courier->id]['CITY']==2 && $c_areas[$courier->id]['REGION']==2) && $c_areas[$courier->id]['ADDRESS']==2) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not city or region with not address
	     if(($c_areas[$courier->id]['CITY']==2 || $c_areas[$courier->id]['REGION']==2) && $c_areas[$courier->id]['ADDRESS']==2) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not city and region
	     if($c_areas[$courier->id]['CITY']==2 && $c_areas[$courier->id]['REGION']==2) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not city or region
	     if($c_areas[$courier->id]['CITY']==2 || $c_areas[$courier->id]['REGION']==2) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find address
	     if($c_areas[$courier->id]['ADDRESS']==1) {
		 return $courier;
	     }
	 }
	 foreach($couriers as $courier) { //Find not address
	     if($c_areas[$courier->id]['ADDRESS']==2) {
		 return $courier;
	     }
	 }
	 //----------
	 return NULL; //no courier maches;
    }

    public function getCourierWithLabelAttribute()
    {
	 if(!$this->courier)
	    return '';
	 return $this->courier->name;
    }

    public function courier()
    {
        return $this->belongsTo('App\Models\Courier')->withTrashed();
    }

}
