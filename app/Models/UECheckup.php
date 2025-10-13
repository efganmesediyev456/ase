<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UECheckup extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'ue_checkups';
    public $dates = ['deleted_at'];

    public function package()
    {
        return $this->belongsTo('App\Models\Package')->withTrashed();
    }
}

