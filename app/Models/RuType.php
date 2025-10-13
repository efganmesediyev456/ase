<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\RuType
 *
 */
class RuType extends Model
{
    use Rememberable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'ru_types';
    protected $guarded = [];

    public function CustomsType()
    {
        return $this->hasOne('App\Models\CustomsType', 'id', 'customs_type_id');
    }

    public function GetHSNameAttribute()
    {
        return $this->hs_code . ' - ' . $this->name_ru;
    }

    public function GetNameAttribute()
    {
        return $this->name_ru;
    }
}
