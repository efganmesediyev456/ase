<?php

namespace App\Models\Hub;

use App\Models\Admin;
use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Azerpost\AzerpostPackage;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Surat\SuratPackage;
use Illuminate\Database\Eloquent\Model;

class Box extends Model
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    const CARRIERS = [
        'azerpost' => "Azərpoçt",
        'azeriexpress' => "Azəriexpress",
        'surat' => "Sürət Kargo",
        'precinct' => "Precinct",
    ];

    const CARRIERS_MAP = [
        'azerpost' => AzerpostPackage::class,
        'azeriexpress' => AzeriExpressPackage::class,
        'surat' =>  SuratPackage::class,
        'precinct' => PrecinctPackage::class,
    ];

    const STATUSES = [
        0 => "Bağlı",
        1 => "Açıq",
    ];

    protected $fillable = [
        'carrier',
        'user_id',
        'name',
        'barcode',
        'status',
        'closed_at',
    ];

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    public function parcels()
    {
        return $this->hasMany(BoxPackage::class);
    }
}
