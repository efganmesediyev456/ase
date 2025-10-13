<?php

namespace App\Models;

//use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
//    use HasFactory;
    protected $fillable = [
        'updated_at',
        'created_at',
        'response',
        'body',
        'request',
        'method',
        'uri',
    ];

    protected $cast = [
        'request' => 'array'
    ];

    protected $casts = [
        'request' => 'array'
    ];
}
