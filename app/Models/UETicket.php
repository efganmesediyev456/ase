<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UETicket extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'ue_tickets';
    public $dates = ['deleted_at'];

    public function package()
    {
        return $this->belongsTo('App\Models\Package')->withTrashed();
    }

    public function conversations()
    {
        return $this->hasMany('App\Models\UETicketConversations', 'ue_ticket_id');
    }
}

