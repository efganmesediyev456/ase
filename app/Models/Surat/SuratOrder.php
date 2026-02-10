<?php

namespace App\Models\Surat;

use App\Models\Admin;
use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratOrder extends Model
{
    use SoftDeletes;
    use ModelEventLogger;


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
        'surat_office_id',
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
        return $this->hasMany(SuratPackage::class);
    }

    public function acceptedPackages()
    {
        return $this->hasMany(SuratPackage::class)->whereIn('status', [SuratPackage::STATUSES['WAREHOUSE'], SuratPackage::STATUSES['ARRIVEDTOPOINT'], SuratPackage::STATUSES['DELIVERED']]);
    }
    public function arrivedPackages()
    {
        return $this->hasMany(SuratPackage::class)->whereIn('status', [SuratPackage::STATUSES['ARRIVEDTOPOINT'], SuratPackage::STATUSES['DELIVERED']]);
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
