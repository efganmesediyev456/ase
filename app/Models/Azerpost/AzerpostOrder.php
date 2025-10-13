<?php

namespace App\Models\Azerpost;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AzerpostOrder extends Model
{
    use SoftDeletes;

    const STATUSES = [
        "WAITING" => 0,
        "SENDING" => 1,
        "SENT" => 2,
        "IN_PROCESS" => 3,
        "ACCEPTED" => 10,
        "ARRIVEDTOPOINT" => 11,
        "DELIVERED" => 4,
    ];
    protected $fillable = [
        'user_id',
        'user_sent_id',
        'azerpost_office_id',
        'name',
        'barcode',
        'weight',
        'details',
        'is_paid',
        'status',
        'sent_at',
        'first_check_date',
    ];

    public function getFullCodeAttribute()
    {
        return 'ASEX' . $this->id;
    }

    public function packages()
    {
        return $this->hasMany(AzerpostPackage::class);
    }

    public function acceptedPackages()
    {
        return $this->hasMany(AzerpostPackage::class)->whereIn('status', [AzerpostPackage::STATUSES['WAREHOUSE'], AzerpostPackage::STATUSES['ARRIVEDTOPOINT'], AzerpostPackage::STATUSES['DELIVERED']]);
    }

    public function arrivedPackages()
    {
        return $this->hasMany(AzerpostPackage::class)->whereIn('status', [AzerpostPackage::STATUSES['ARRIVEDTOPOINT'], AzerpostPackage::STATUSES['DELIVERED']]);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'user_sent_id', 'id');
    }

    public function azerpostOffice()
    {
        return $this->belongsTo(AzerpostOffice::class);
    }
}
