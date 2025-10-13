<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UETicketConversation extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'ue_ticket_conversations';
    public $dates = ['deleted_at'];

    public function ticket()
    {
        return $this->belongsTo('App\Models\UETicket', 'ue_ticket_id')->withTrashed();
    }
}

