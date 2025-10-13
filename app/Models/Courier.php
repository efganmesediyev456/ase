<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Traits\ModelEventLogger;
use App\Traits\Password;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Courier extends Authenticatable
{
    use Password;
    use SoftDeletes;
    use ModelEventLogger;

    protected static function boot()
    {
        parent::boot();
        static::created(function ($query) {
        });
        static::updating(function ($query) {
           $old_cr=Courier::find($query->id);
           if(isset($query->password) && !empty($query->password) && $old_cr->password != $query->password) {
                $query->remember_token=Str::random(60);
           }
        });
    }

    public function cds()
    {
        return $this->hasMany('App\Models\CD');
    }

    public function areas()
    {
        return $this->hasMany('App\Models\CA');
    }

    public function location_log()
    {
        return $this->hasOne('App\Models\CourierLocationLog', 'id','location_log_id');
    }

    public function customers()
    {
        return $this->hasMany('App\Models\Customer');
    }

    public function getLocationUrlAttribute() {
        $cl=$this->location_log;
        return $cl && $cl->latitude && $cl->longitude ? 'https://maps.google.com/?q='.$cl->latitude.','.$cl->longitude :  NULL;
    }

    public function getLocationUrl2Attribute($value)
    {
        $cl=$this->location_log;
        if( $cl && $cl->latitude && $cl->longitude )
        {
           $html='<a target="_blank" style="text-decoration: none;" href="'.$this->location_url.'"><font color="blue">'.$cl->updated_at.'</font></a>';
           echo $html;
           return;
        }
        return 'No';
    }
}
