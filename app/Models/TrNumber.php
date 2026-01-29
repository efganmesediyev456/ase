<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrNumber extends Model
{

    protected $table = "trendyol_phone_assignments";


    public function assignedUser(){
        return $this->belongsTo(User::class,'assigned_user_id','id');
    }

}
