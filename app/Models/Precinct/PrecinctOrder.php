<?php

namespace App\Models\Precinct;

use App\Models\Admin;
use App\Models\DeliveryPoint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrecinctOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_sent_id',
        'precinct_office_id',
        'name',
        'barcode',
        'weight',
        'details',
        'is_paid',
        'status',
        'sent_at',
        'first_check_date',
    ];

    const STATUSES = [
        "WAITING" => 0,
        "SENDING" => 1,
        "SENT" => 2,
        "IN_PROCESS" => 5,
        "ACCEPTED" => 3,
        "ARRIVEDTOPOINT" => 11,
        "DELIVERED" => 4,
    ];

    public function getFullCodeAttribute()
    {
        return 'ASEX' . $this->id;
    }

    public function packages()
    {
        return $this->hasMany(PrecinctPackage::class);
    }

    public function acceptedPackages()
    {
        return $this->hasMany(PrecinctPackage::class)->where('status', PrecinctPackage::STATUSES['ARRIVEDTOPOINT']);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class,  'user_sent_id', 'id');
    }

    public function precinctOffice()
    {
        return $this->belongsTo(DeliveryPoint::class, 'precinct_office_id', 'id');
    }
}
