<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageTrack extends Model
{
    use SoftDeletes;

    public $with = ['warehouse', 'user.dealer', 'country'];
    protected $table = "all_v";

    /**
     * @var array
     */
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

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Customer')->withTrashed();
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

    public function getStatusWithLabelAttribute()
    {
        if ($this->attributes['track'])
            return config('ase.attributes.track.statusShort')[$this->attributes['status']];
        else
            return config('ase.attributes.package.status')[$this->attributes['status']];
    }

}
