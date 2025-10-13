<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PackageLog
 *
 * @property int $id
 * @property int $package_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Package $package
 * @method static Builder|PackageLog whereCreatedAt($value)
 * @method static Builder|PackageLog whereId($value)
 * @method static Builder|PackageLog whereMetaKey($value)
 * @method static Builder|PackageLog whereMetaValue($value)
 * @method static Builder|PackageLog wherePackageId($value)
 * @method static Builder|PackageLog whereUpdatedAt($value)
 * @mixin Eloquent
 * @property-read Admin $admin
 * @property-read mixed $json_data
 * @property-read Warehouse $warehouse
 * @property int|null $warehouse_id
 * @property int|null $admin_id
 * @property string $data
 * @method static Builder|PackageLog whereAdminId($value)
 * @method static Builder|PackageLog whereData($value)
 * @method static Builder|PackageLog whereWarehouseId($value)
 * @method static Builder|PackageLog newModelQuery()
 * @method static Builder|PackageLog newQuery()
 * @method static Builder|PackageLog query()
 */
class PackageLog extends Model
{
    protected $appends = ['json_data'];

    protected $with = ['warehouse', 'admin'];

    protected $fillable = [
        "warehouse_id",
        "admin_id",
        "package_id",
        "data",
    ];

    /**
     * @return BelongsTo
     */
    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    /**
     * @return BelongsTo
     */
    public function track()
    {
        return $this->belongsTo('App\Models\Track', 'package_id');
    }

    public function admin()
    {
        return $this->belongsTo('App\Models\Admin');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse');
    }

    public function getJsonDataAttribute()
    {
        return json_decode(rtrim($this->attributes['data']), true);
    }

    public function getDataAttribute()
    {
        $arr = json_decode($this->attributes['data'], true);
        $data = "<ul>";
        foreach ($arr as $key => $value) {
            $str = '';

            if (is_string($value)) {
                $data .= "<li><b>" . $key . "</b> : <i>" . str_limit($value, 130) . $str . "</i></li>";
            } else if (is_array($value)) {
                foreach ($value as $_key => $_value) {
                    if ($_key == "before") {
                        $_value = config('ase.attributes.package.status')[$value['before']] ?? null . '(' . $value['before'] . ')';
                    }
                    if ($_key == "after") {
                        $_value = config('ase.attributes.package.status')[$value['after']] ?? null . '(' . $value['after'] . ')';
                    }
                    $data .= "<li><b>" . $_key . "</b> : <i>" . str_limit($_value, 130) . "</i></li>";
                }
            } else {
                $data .= "<li><b>" . $key . "</b> : <i>" . $value . $str . "</i></li>";
            }
        }
        $data .= "</ul>";

        return $data;
    }
}
