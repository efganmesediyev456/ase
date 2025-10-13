<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilialKey extends Model
{
    use SoftDeletes;

    protected $table = "filial_keys";
    public $dates = ['deleted_at'];

}
