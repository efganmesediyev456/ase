<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use SoftDeletes;
    use ModelEventLogger;
    //public $with=['partner','airboxes', 'tracks'];
    //public $withCount=['airboxes','tracks','trackcarriers','trackcarriersreg', 'trackcarriersdepesh','track_not_completed'];
    public $with=[];
    public $withCount=[];


    protected $fillable = [
        'name',        // Waybill name
        'partner_id',  // Partner identifier
        'created_at',  // Timestamp of creation
        'updated_at',  // Timestamp of creation
        'from_country',
        'first_scanned_at',
        'scanned_cnt'
    ];

    public $dates = ['deleted_at'];

    public function airboxes()
    {
        return $this->hasMany('App\Models\Airbox');
    }

    public function tracks()
    {
        return $this->hasMany('App\Models\Track');
    }

    public function track_not_completed()
    {
        //return $this->hasMany('App\Models\Track')->whereRaw('(status not in (16,17,20,21,22,23,24)  and status > 12)');
        //return $this->hasMany('App\Models\Track')->whereRaw('(status not in (16,17,20,21,22,23,24))');
        return $this->hasMany('App\Models\Track')->whereNull('scanned_at')->whereNotIn('status',[16,17,18,19,20,21,22,23,24,46,45]);
    }

    public function trackcarriers()
    {
        return  $this->hasManyThrough(PackageCarrier::class,Track::class, 'container_id', 'track_id','id','id')->whereRaw('(check_customs=0 or code in (200,400))');
    }

    public function trackcarriersdepesh()
    {
        return  $this->hasManyThrough(PackageCarrier::class,Track::class, 'container_id', 'track_id','id','id')->whereRaw('(depesH_NUMBER is not null or check_customs=0)');
    }

    public function trackcarriersreg()
    {
        return  $this->hasManyThrough(PackageCarrier::class,Track::class, 'container_id', 'track_id','id','id')->whereRaw('(ecoM_REGNUMBER is not null or check_customs=0)');
    }

    public function getPartnerWithLabelAttribute()
    {
        $partner_id=$this->attributes['partner_id'];
        $arr=config('ase.attributes.track.partner');
        if(array_key_exists($partner_id,$arr))
            return $arr[$partner_id];
        return '';
    }

    public function getSentWithLabelAttribute()
    {
	if($this->sent) return "Sent";
	return "";
    }

    public function getStatusWithLabelAttribute()
    {
        $arr=config('ase.attributes.track.status');
	$depesh_status=$this->depesh_start ? " DEPESH STARTED" : "";
	if($this->status) {
	    if(array_key_exists($this->status,$arr)) return $arr[$this->status];
	    return "Unknown(".$this->status.")".$depesh_status;
	}
	if($this->sent) return "SENT".$depesh_status;
	return $depesh_status;
    }

    public function partner()
    {
         return $this->belongsTo('App\Models\Partner');
    }
}
