<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoLog extends Model
{
    protected $table = "promo_logs";

    public function getActivationWithLabelAttribute()
    {
        return config('ase.attributes.promo.activation')[$this->attributes['activation']];
    }

    public function promo()
    {
        return $this->belongsTo('App\Models\Promo')->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }

    public function package()
    {
        return $this->belongsTo('App\Models\Package')->withTrashed();
    }

    public function getCountryFlagAttribute()
    {
        $country = $this->defaultCountry();

        return $country ? $country->flag : null;
    }

    public function defaultCountry()
    {
        return ($this->warehouse and $this->warehouse->country) ? $this->warehouse->country : ($this->country ? $this->country : null);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*    protected static function boot()
        {
            parent::boot();

            static::creating(function ($query) {
                $query->user_id =  \Auth::user()->id;
            });
    }*/

}
