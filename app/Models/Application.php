<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    public $uploadDir = 'uploads/stores/';
    protected $guarded = [];
}