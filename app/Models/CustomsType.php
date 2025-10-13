<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\CustomsType
 *
 */
class CustomsType extends Model
{
    use Rememberable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $table = 'customs_types';

    public function getNameEnOneAttribute()
    {
        $dname = $this->name_en;
        if ((empty($dname) || strtoupper($dname) == "OTHERS" || strtoupper($dname) == "OTHER") && !empty($this->parent_id) && $this->pcustomstype)
            $dname = $this->pcustomstype->name_en;
        return $dname;
    }

    public function getNameEnParentAttribute()
    {
        $dname = $this->name_en;
        if (!empty($this->parent_id) && $this->pcustomstype)
            $dname = $this->pcustomstype->name_en;
        return $dname;
    }

    public function getNameAzWithParentAttribute()
    {
        $dname = $this->name_az;
        if (!empty($this->parent_id) && $this->pcustomstype)
            $dname = $this->pcustomstype->name_az . ' / ' . $dname;
        return $dname;
    }

    public function getNameEnWithParentAttribute()
    {
        $dname = $this->name_en;
        if (!empty($this->parent_id) && $this->pcustomstype)
            $dname = $this->pcustomstype->name_en . ' / ' . $dname;
        return $dname;
    }

    public function getNameRuWithParentAttribute()
    {
        $dname = $this->name_ru;
        if (!empty($this->parent_id) && $this->pcustomstype)
            $dname = $this->pcustomstype->name_ru . ' / ' . $dname;
        return $dname;
    }

    public function getDisplayNameAttribute()
    {
        $dname = $this->name_az_with_parent . " | " . $this->name_en_with_parent . " | " . $this->name_ru_with_parent;
        return $dname;
    }

    public function pcustomstype()
    {
        return $this->belongsTo('App\Models\CustomsType', 'parent_id', 'id');
    }
}
