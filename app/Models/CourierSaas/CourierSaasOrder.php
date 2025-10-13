<?php

namespace App\Models\CourierSaas;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CourierSaasOrder extends Model
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
        'office',
        'name',
        'barcode',
        'weight',
        'details',
        'is_paid',
        'status',
        'sent_at',
    ];

    public function getFullCodeAttribute()
    {
        return 'ASEX' . $this->id;
    }

    public function packages()
    {
        return $this->hasMany(CourierSaasPackage::class);
    }

    public function acceptedPackages()
    {
        return $this->hasMany(CourierSaasPackage::class)->whereIn('status', [CourierSaasPackage::STATUSES['WAREHOUSE'], CourierSaasPackage::STATUSES['ARRIVEDTOPOINT'], CourierSaasPackage::STATUSES['DELIVERED']]);
    }
    public function arrivedPackages()
    {
        return $this->hasMany(CourierSaasPackage::class)->whereIn('status', [CourierSaasPackage::STATUSES['ARRIVEDTOPOINT'], CourierSaasPackage::STATUSES['DELIVERED']]);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'user_sent_id', 'id');
    }

    public function suratOffice()
    {
        return $this->belongsTo(SuratOffice::class);
    }
}
