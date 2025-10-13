<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogicSync extends Model
{

    public $package_name = '';
    public $bag_name = '';
    public $status = '';
    public $status_id = 0;
    public $num = 0;
    public $sender = '';

    public function getSenderAttribute()
    {
        return $this->sender;
    }

    public function getNumAttribute()
    {
        return $this->num;
    }

    public function getStatusIdAttribute()
    {
        return $this->status_id;
    }

    public function getStatusAttribute()
    {
        return $this->status;
    }

    public function getPackageNameAttribute()
    {
        return $this->package_name;
    }

    public function getBagNameAttribute()
    {
        return $this->bag_name;
    }

    public function package()
    {
        return $this->hasOne('App\Models\Package', 'id', 'package_id');
    }

}
