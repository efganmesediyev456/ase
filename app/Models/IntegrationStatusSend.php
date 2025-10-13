<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IntegrationStatusSend extends Model
{
    protected $table = 'integration_send_statuses';

    protected $guarded = [
       'id'
    ];

}
