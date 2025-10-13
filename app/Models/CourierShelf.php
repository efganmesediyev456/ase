<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierShelf extends Model
{
    protected $table = "courier_shelf";

    public function courier()
    {
        return $this->belongsTo(Courier::class)->withTrashed();
    }

}
