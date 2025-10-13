<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ZipCode extends Model
{
    use SoftDeletes;

    protected $table = "zip_codes";
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];


}
