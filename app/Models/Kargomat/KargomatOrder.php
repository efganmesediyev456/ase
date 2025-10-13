<?php

namespace App\Models\Kargomat;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KargomatOrder extends Model
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
        'kargomat_office_id',
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
        return $this->hasMany(KargomatPackage::class);
    }

    public function acceptedPackages()
    {
        return $this->hasMany(KargomatPackage::class)->whereIn('status', [KargomatPackage::STATUSES['WAREHOUSE'], KargomatPackage::STATUSES['ARRIVEDTOPOINT'], KargomatPackage::STATUSES['DELIVERED']]);
    }
    public function arrivedPackages()
    {
        return $this->hasMany(KargomatPackage::class)->whereIn('status', [KargomatPackage::STATUSES['ARRIVEDTOPOINT'], KargomatPackage::STATUSES['DELIVERED']]);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'user_sent_id', 'id');
    }

    public function kargomatOffice()
    {
        return $this->belongsTo(KargomatOffice::class);
    }
}
