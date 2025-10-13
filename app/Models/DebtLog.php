<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DebtLog extends Model
{
    protected $fillable = [
        'custom_id', 'price', 'after_price', 'created_at', 'updated_at',
    ];
}
