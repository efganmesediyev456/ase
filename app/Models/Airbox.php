<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Airbox extends Model
{
    use SoftDeletes;
    use ModelEventLogger;


    public $dates = ['deleted_at'];

    public $with = ['tracks'];

    public $withCount = ['tracks', 'trackcarriers', 'trackcarriersreg', 'trackcarriersdepesh','track_not_completed'];

    protected $fillable = [
        'partner_id', 'container_id', 'name', 'total_weight', 'total_count','from_country'
    ];


    public function getStatusWithLabelAttribute()
    {
         $arr=config('ase.attributes.track.status');
         if($this->status) {
             if(array_key_exists($this->status,$arr)) return $arr[$this->status];
                 return "Unknown(".$this->status.")";
         }
         return "";
    }

    public function container()
    {
        return $this->belongsTo('App\Models\Container')->withTrashed();
    }

    public function tracks()
    {
        return $this->hasMany('App\Models\Track');
    }

    public function track_not_completed()
    {
         return $this->hasMany('App\Models\Track')->whereRaw('(status not in (16,17,20,21,22,23) and status>12)');
    }

    public function trackcarriers()
    {
        return  $this->hasManyThrough(PackageCarrier::class,Track::class, 'airbox_id', 'track_id','id','id')->whereRaw('(check_customs=0 or code in (200,400))');
    }

    public function trackcarriersdepesh()
    {
        return  $this->hasManyThrough(PackageCarrier::class,Track::class, 'airbox_id', 'track_id','id','id')->whereRaw('(depesH_NUMBER is not null or check_customs=0)');
    }

    public function trackcarriersreg()
    {
        return  $this->hasManyThrough(PackageCarrier::class,Track::class, 'airbox_id', 'track_id','id','id')->whereRaw('(ecoM_REGNUMBER is not null or check_customs=0)');
    }

    public function getPartnerWithLabelAttribute()
    {
        $partner_id = $this->attributes['partner_id'];
        $arr = config('ase.attributes.track.partner');
        if (array_key_exists($partner_id, $arr))
            return $arr[$partner_id];
        return '';
    }
}
