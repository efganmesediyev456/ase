<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Eloquent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

//use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\PackageCarrier
 *
 * @property int $id
 * @property string $custom_id
 * @property-read Parcel $parcel
 * @property-read Collection|Package[] $packages
 * @property-read int|null $packages_count
 * @property-read Collection|Package[] $waiting
 * @property-read Collection|Package[] $packagecarriers
 * @property-read Collection|Package[] $packagecarriersdepesh
 * @property-read Collection|Package[] $packagecarriersreg
 * @property-read int|null $waiting_count
 * @property-read int|null $packagecarriers_count
 * @property-read int|null $packagecarriersdepesh_count
 * @property-read int|null $packagecarriersreg_count
 * @mixin Eloquent
 */
class Bag extends Model
{
    public $with = ['packages'];
//    use SoftDeletes;
    use ModelEventLogger;

    public $withCount = [
        'packages', 'waiting', 'packagecarriers', 'packagecarriersreg', 'packagecarriersdepesh'
    ];

    protected $fillable = [
        'parcel_id', 'custom_id'
    ];

    protected $table = 'bags';

    protected $primaryKey = 'id';

    public function parcel()
    {
        //return $this->belongsTo('App\Models\Parcel');
        //return $this->belongsToMany(Parcel::class, 'parcel_package', 'package_id');
        return $this->belongsToMany(Parcel::class);
    }

    public function packages()
    {
        //return $this->hasMany(Package::class)->orderBy('status', 'asc');
        return $this->belongsToMany(Package::class, 'bag_package')->orderBy('status', 'asc');
    }

    public function waiting()
    {
        //return $this->hasMany(Package::class)->where('status', 1);
        return $this->belongsToMany(Package::class, 'bag_package')->where('status', 1);
    }

    public function packagecarriers()
    {
        //return $this->belongsToMany(PackageCarrier::class, 'bag_package','bag_id','package_id')->whereIn('code', [200, 400])->orWhere('check_customs',0);
        return $this->belongsToMany(PackageCarrier::class, 'bag_package', 'bag_id', 'package_id')->whereRaw('(code in (200, 400) or check_customs=0)');
    }

    public function packagecarriersdepesh()
    {
        //return $this->belongsToMany(PackageCarrier::class, 'bag_package','bag_id','package_id')->whereNotNull('depesH_NUMBER')->orWhere('check_customs',0);
        return $this->belongsToMany(PackageCarrier::class, 'bag_package', 'bag_id', 'package_id')->whereRaw('(depesH_NUMBER is not null or check_customs=0)');
    }

    public function packagecarriersreg()
    {
        //return $this->belongsToMany(PackageCarrier::class, 'bag_package','bag_id','package_id')->whereNotNull('ecoM_REGNUMBER')->orWhere('check_customs',0);
        return $this->belongsToMany(PackageCarrier::class, 'bag_package', 'bag_id', 'package_id')->whereRaw('(ecoM_REGNUMBER is not null or check_customs=0)');
    }

}
