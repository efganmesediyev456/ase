<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;

/**
 * App\Models\PackageCarrier
 *
 * @property int $id
 * @property int $package_id
 * @property int $code
 * @property-read Package $package
 * @method static Builder|PackageCarrier withTrashed()
 * @method static Builder|PackageCarrier withoutTrashed()
 * @mixin Eloquent
 */
class PackageCarrier extends Model
{
    use SoftDeletes;

    //use ModelEventLogger;
    protected $table = 'package_carriers';
    protected $primaryKey = 'package_id';
    public $dates = ['deleted_at'];

    public function carrier()
    {
        return $this->hasOne('App\Models\PackageCarrier', 'id', 'id');
    }

    public function package()
    {
        return $this->hasOne('App\Models\Package', 'id', 'package_id');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function scopeIncustoms($query, $st = 2)
    {
        if ($st == 2) {
            return $query->whereIn('package_carriers.code', [200, 400])->whereNotNull('package_carriers.ecoM_REGNUMBER');
        }
        if ($st == 4) {
            return $query->whereIn('package_carriers.code', [200, 400])->whereNull('package_carriers.ecoM_REGNUMBER');
        } else if ($st == 3) {
            return $query->whereIn('package_carriers.code', [200, 400])->whereNotNull('package_carriers.depesH_NUMBER');
        } else if ($st == 5) {
            return $query->whereIn('package_carriers.code', [200, 400])->whereNull('package_carriers.depesH_NUMBER');
        } else if ($st == 1) {
            return $query->whereIn('package_carriers.code', [200, 400]);
        } else if ($st == 0) {
            return $query->whereNull('package_carriers.id')->orWhereNotIn('package_carriers.code', [200, 400]);
        }
    }
}
