<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\PackageTypeTranslation
 *
 * @property int $id
 * @property int $package_type_id
 * @property string $name
 * @property string $locale
 * @method static Builder|PackageTypeTranslation whereId($value)
 * @method static Builder|PackageTypeTranslation whereLocale($value)
 * @method static Builder|PackageTypeTranslation whereName($value)
 * @method static Builder|PackageTypeTranslation wherePackageTypeId($value)
 * @mixin Eloquent
 * @method static Builder|PackageTypeTranslation newModelQuery()
 * @method static Builder|PackageTypeTranslation newQuery()
 * @method static Builder|PackageTypeTranslation query()
 */
class PackageTypeTranslation extends MainTranslate
{
    /**
     * @var array
     */
    protected $fillable = ['name'];
}
