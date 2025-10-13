<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tariff extends Model
{
    use SoftDeletes;
    use Translatable;
    use ModelEventLogger;

    protected $with = ['translations'];
    public $translatedAttributes = ['name'];
    protected $table = "tariffs";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];

    public function getActiveAttribute()
    {
        if ($this->is_active)
            return 'YES';
        return 'NO';
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }

    public function tariff_weights()
    {
        return $this->hasMany('App\Models\TariffWeight')->orderBy('from_weight', 'asc');
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
