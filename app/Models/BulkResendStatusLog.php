<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkResendStatusLog extends Model
{
    protected $table = 'bulk_resend_status_logs';

    protected $guarded = [];

    protected $casts = [
        'executed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function trackStatus()
    {
        return $this->belongsTo(TrackStatus::class);
    }
}
