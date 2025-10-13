<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Point extends Model
{

    protected $table = 'points';
    protected $fillable = ['courier_id', 'courier_name', 'lat', 'lon'];


}
