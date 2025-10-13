<?php

namespace App\Models;
use Eloquent;

class CareerTranslation extends MainTranslate
{
    protected $fillable = ['name','career_id','locale','city'];
}