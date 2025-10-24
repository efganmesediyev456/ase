<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TariffWeight extends Model
{
    use SoftDeletes;
    use Translatable;
    use ModelEventLogger;

    protected $with = ['translations'];
    public $translatedAttributes = ['name'];
    protected $table = "tariff_weights";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];

    public function tariff()
    {
        return $this->belongsTo('App\Models\Tariff')->withTrashed();
    }

    public function tariff_prices()
    {
        return $this->hasMany('App\Models\TariffPrice');
    }

    public function non_azerpoct_tariff_prices()
    {
        return $this->hasMany('App\Models\TariffPrice')->where('azerpoct', 0);
    }

    public function withoutAzerpoctTariffPrices(){
        return $this->hasMany('App\Models\TariffPrice');
    }

    public function azerpoct_tariff_prices()
    {
        return $this->hasMany('App\Models\TariffPrice')->where('azerpoct', 1);
    }

}
