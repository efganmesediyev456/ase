<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierShelfLog extends Model
{
    protected $table = "courier_shelf_logs";

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class, 'admin_id');
    }

}
