<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    public $with = ['country'];

    protected $fillable = [
        'name',
        'address',
    ];

    public function country()
    {
         return $this->belongsTo('App\Models\Country');
    }
}
