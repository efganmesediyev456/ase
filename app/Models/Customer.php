<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $fillable = [
        'partner_id',
        'fin',
        'address',
        'region_name',
        'company_name',
        'city_id',
        'city_name',
        'zip_code',
        'email',
        'courier_id',
        'phone',
        'surname',
        'name',
        'fullname',
        'error_at',
    ];

    //public $with = [];

    public $dates = ['deleted_at'];

    public function city()
    {
        return $this->belongsTo('App\Models\City')->withTrashed();
    }

    public function getPartnerWithLabelAttribute()
    {
        $partner_id=$this->attributes['partner_id'];
        $arr=config('ase.attributes.track.partner');
        if(array_key_exists($partner_id,$arr))
            return $arr[$partner_id];
        return '';
    }

    public function getCustomerIdAttribute()
    {
        $id = $this->attributes['id'];
        return 'ASE' . str_pad($id, 6, '0', STR_PAD_LEFT);
    }

    public function getFullnameAttribute()
    {
        if($this->name != null) {
            return $this->name . ' ' . $this->surname;
        }
        return $this->attributes['fullname'];
    }

    public function courier()
    {
        return $this->belongsTo('App\Models\Courier');
    }
}
