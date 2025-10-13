<?php

namespace App\Models;

use Carbon\Carbon;
use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeightPrice extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = "weight_prices";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];

    public function getActiveAttribute()
    {
         if($this->is_active)
              return 'YES';
          return 'NO';
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
}
