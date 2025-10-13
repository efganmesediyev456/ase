<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappTemplate extends Model
{
    use SoftDeletes;

    protected $table = "whatsapp_templates";
    protected $fillable = [
        "key", "name", "content", "content_sms"
    ];
}
