<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnknownOffice extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    public $dates = ['deleted_at'];

    protected $table = "unknown_offices";

    public function getCityNameAttribute()
    {
        if (!$this->city_id)
            return "-";
        $city = $this->city();
        if ($city)
            return $city->first()->name;
        return "-";
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
