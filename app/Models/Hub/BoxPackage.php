<?php

namespace App\Models\Hub;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;

class BoxPackage extends Model
{
    protected $fillable = [
        'box_id',
        'parcel_id',
        'user_id',
        'parcel_type',
        'tracking',
    ];

    public function box()
    {
        return $this->belongsTo(Box::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

}
