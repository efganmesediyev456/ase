<?php

namespace App\Models\Azerpost;

use App\Models\Admin;
use App\Models\Package;
use App\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AzerpostPackage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'azerpost_order_id',
        'package_id',
        'type',
        'barcode',
        'user_id',
        'added_by',
        'pin_code',
        'status',
        'comment',
        'payment_status',
        'foreign_order_id',
        'sent_at',
        'accepted_at',
        'arrived_at',
        'delivered_at',
        'deleted_at',
        'company_sent'
    ];

    protected $dates = [
        'sent_at',
        'accepted_at',
        'arrived_at',
        'delivered_at',
        'deleted_at'
    ];

    const STATUSES = [
        "HAS_PROBLEM" => 0,
        "NOT_SENT" => 1,
        "WAITING" => 2,
        "SENT" => 3,
        "IN_PROCESS" => 4,
        "WAREHOUSE" => 6,
        "ARRIVEDTOPOINT" => 8,
        "DELIVERED" => 10,
    ];

    public function scopePayed()
    {
        return $this->where('payment_status', true);
    }

    public function scopeNotPayed()
    {
        return $this->where('payment_status', false);
    }

    public function container()
    {
        return $this->belongsTo(AzerpostOrder::class, 'azerpost_order_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'added_by', 'id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class, 'package_id');
    }

    public function scopeTemuTrack()
    {
        return $this->belongsTo(Track::class, 'package_id')->where('partner_id', 8);
    }

    public function scopeNotTemuTrack()
    {
        return $this->belongsTo(Track::class, 'package_id')->where('partner_id', 8);
    }
}
