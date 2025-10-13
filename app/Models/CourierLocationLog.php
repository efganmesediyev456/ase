<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierLocationLog extends Model
{
    use SoftDeletes;

    protected $table = "courier_location_logs";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];


}
