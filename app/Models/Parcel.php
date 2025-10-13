<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Parcel
 *
 * @property int $id
 * @property int $warehouse_id
 * @property string $custom_id
 * @property int $sent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Package[] $packages
 * @property-read Collection|Bag[] $bags
 * @property-read int|null $packages_count
 * @property-read int|null $bags_count
 * @property-read Warehouse $warehouse
 * @method static Builder|Parcel newModelQuery()
 * @method static Builder|Parcel newQuery()
 * @method static Builder|Parcel query()
 * @method static Builder|Parcel whereCreatedAt($value)
 * @method static Builder|Parcel whereCustomId($value)
 * @method static Builder|Parcel whereId($value)
 * @method static Builder|Parcel whereSent($value)
 * @method static Builder|Parcel whereUpdatedAt($value)
 * @method static Builder|Parcel whereWarehouseId($value)
 * @mixin Eloquent
 * @property-read Collection|Package[] $waiting
 * @property-read Collection|Package[] $packagecarriers
 * @property-read Collection|Package[] $packagecarriersdepesh
 * @property-read Collection|Package[] $packagecarriersreg
 * @property-read int|null $waiting_count
 * @property-read int|null $packagecarriers_count
 * @property-read int|null $packagecarriersdepesh_count
 * @property-read int|null $packagecarriersreg_count
 * @property-read mixed $sent_with_label
 */
class Parcel extends Model
{
    protected $fillable = ['warehouse_id', 'custom_id'];

    public static function generateCustomId($digits = 8)
    {
        do {

            $code = str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

            $check = Parcel::whereCustomId($code)->first();
            if (!$check) {
                break;
            }
        } while (true);

        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $query->custom_id = $query->custom_id ?: self::generateCustomId();
        });
    }

    public function bags()
    {
        return $this->hasMany(Bag::class);
        //return $this->belongsToMany(Bag::class,'v_bags','parcel_id','id');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'parcel_package')->orderBy('status', 'asc');
    }

    public function waiting()
    {
        return $this->belongsToMany(Package::class, 'parcel_package')->where('status', 1);
    }

    public function getReadyCntAttribute()
    {
        $packages = Package::where('warehouse_id', $this->warehouse_id);
        $packages = $packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE');
        $packages = $packages->incustoms()->ready()->where('packages.status', 0)->count();
        return $packages;
    }

    public function packagecarriers()
    {
        //return $this->belongsToMany(PackageCarrier::class, 'parcel_package','parcel_id','package_id')->whereIn('code', [200, 400])->orWhere('check_customs',0);
        return $this->belongsToMany(PackageCarrier::class, 'parcel_package', 'parcel_id', 'package_id')->whereRaw('(check_customs=0 or code in (200,400))');
    }

    public function packagecarriersdepesh()
    {
        //return $this->belongsToMany(PackageCarrier::class, 'parcel_package','parcel_id','package_id')->whereNotNull('depesH_NUMBER')->orWhere('check_customs',0);
        return $this->belongsToMany(PackageCarrier::class, 'parcel_package', 'parcel_id', 'package_id')->whereRaw('(depesH_NUMBER is not null or check_customs=0)');
    }

    public function packagecarriersreg()
    {
        //return $this->belongsToMany(PackageCarrier::class, 'parcel_package','parcel_id','package_id')->whereNotNull('ecoM_REGNUMBER')->orWhere('check_customs',0);
        return $this->belongsToMany(PackageCarrier::class, 'parcel_package', 'parcel_id', 'package_id')->whereRaw('(ecoM_REGNUMBER is not null or check_customs=0)');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getCountryCodeAttribute()
    {
        return $this->warehouse->country->code;
    }

    public function getSentWithLabelAttribute()
    {
        if ($this->sent <= 1) {
            $all = $this->packages->count();
            $inBaku = $this->packages->where('status', '>', 1)->where('status','!=',7)->count();
            if ($inBaku / $all > 0.25) {
                $this->sent = 2;
                $this->save();
            }
        }

	if($this->departed && !$this->sent) {
            return config('ase.attributes.package.status.7');
	} else {
            return config('ase.attributes.package.status.' . $this->sent);
	}
    }

    public function getNotInsertedAttribute()
    {
        return $this->inserted == 0 && $this->warehouse_id == env('LOGIC_ID');
    }


    public function getIsNotLogicAttribute()
    {
        return $this->warehouse_id != env('LOGIC_ID');
    }

    public function getInsertedColorAttribute()
    {
        return $this->inserted == 0 ? '#ffdaeb7d' : ($this->inserted == 1 ? '#dae9ff94' : '#c9ffd06b');
    }
}
