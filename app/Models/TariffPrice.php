<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TariffPrice extends Model
{
    use SoftDeletes;

    //use Translatable;
    use ModelEventLogger;

    protected $with = ['city'];//,'translations'];
    //public $translatedAttributes = ['name'];
    protected $table = "tariff_prices";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];

    public function getAzerpoct1Attribute()
    {
        if ($this->azerpoct)
            return 'YES';
        return 'NO';
    }

    public function tariff_weight()
    {
        return $this->belongsTo('App\Models\TariffWeight')->withTrashed();
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function getCityNameAttribute()
    {
        return isset($this->attributes['city_id']) ? ($this->city()->first() ? $this->city()->first()->name : '-') : '-';
    }
}
