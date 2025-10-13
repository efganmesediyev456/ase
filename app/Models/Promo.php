<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Promo extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = "promos";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];

    public function getActiveAttribute()
    {
        if ($this->is_active)
            return 'YES';
        return 'NO';
    }

    public function getActivationWithLabelAttribute()
    {
        return config('ase.attributes.promo.activation')[$this->attributes['activation']];
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

    public function defaultCountry()
    {
        return ($this->warehouse and $this->warehouse->country) ? $this->warehouse->country : ($this->country ? $this->country : null);
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            $query->admin_id = auth()->guard('admin')->check() ? auth()->guard('admin')->user()->id : null;
        });
    }

}
