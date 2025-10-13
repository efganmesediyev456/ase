<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageTrackInBaku extends Model
{
    use SoftDeletes;

    public $with = ['warehouse', 'user.dealer', 'country'];
    protected $table = "inbaku_v";

    public $dates = ['deleted_at'];


    public function getPackageFilialNameAttribute()
    {
        if($this->p_ao_description) return $this->p_ao_description.' (AZXP)';
        if($this->p_dp_description) return $this->p_dp_description.' (ASE)';
    }

    public function getUserFilialNameAttribute()
    {
        if($this->ao_description) return $this->ao_description.' (AZXP)';
        if($this->dp_description) return $this->dp_description.' (ASE)';
    }

    public function getStatusWithLabelAttribute()
    {
        $status=$this->attributes['status'];
        $track=$this->attributes['track'];
	if($track == 1) {
            if(!$status)
                  return '';
             $arr=config('ase.attributes.track.statusShort');
             if(array_key_exists($status,$arr))
                   return $arr[$status];
             $arr=config('ase.attributes.track.status');
             if(array_key_exists($status,$arr))
	           return $arr[$status];
             return 'Unknown ('.$status.')';
	}
        return config('ase.attributes.package.status')[$status];
    }

    public function getPaidWithLabelAttribute()
    {
            return config('ase.attributes.package.paid')[$this->attributes['paid']];
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function courier_delivery()
    {
        return $this->belongsTo('App\Models\CD')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }

    public function getCountryFlagAttribute()
    {
        $country = $this->defaultCountry();

        return $country ? $country->flag : null;
    }

}
