<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
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
class PackageAzerPoct extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'package_azerpoct';
    protected $primaryKey = 'package_id';

    public function package()
    {
        return $this->hasOne('App\Models\Package', 'package_id');
    }
}
