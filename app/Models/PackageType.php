<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\PackageType
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string|null $icon
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Package[] $packages
 * @property-read PackageType|null $parent
 * @property-read Collection|PackageType[] $sub
 * @property-read Collection|PackageTypeTranslation[] $translations
 * @method static Builder|PackageType listsTranslations($translationField)
 * @method static Builder|PackageType notTranslatedIn($locale = null)
 * @method static Builder|PackageType orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|PackageType orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|PackageType translated()
 * @method static Builder|PackageType translatedIn($locale = null)
 * @method static Builder|PackageType whereCreatedAt($value)
 * @method static Builder|PackageType whereIcon($value)
 * @method static Builder|PackageType whereId($value)
 * @method static Builder|PackageType whereParentId($value)
 * @method static Builder|PackageType whereTranslation($key, $value, $locale = null)
 * @method static Builder|PackageType whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|PackageType whereUpdatedAt($value)
 * @method static Builder|PackageType withTranslation()
 * @mixin Eloquent
 * @property-read Collection|PackageType[] $children
 * @property Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|PackageType onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|PackageType whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|PackageType withTrashed()
 * @method static \Illuminate\Database\Query\Builder|PackageType withoutTrashed()
 * @property-read int|null $children_count
 * @property-read int|null $packages_count
 * @property-read int|null $translations_count
 * @method static Builder|PackageType newModelQuery()
 * @method static Builder|PackageType newQuery()
 * @method static Builder|PackageType query()
 */
class PackageType extends Model
{
    use Translatable;
    use Rememberable;
    use SoftDeletes;

    /**
     * @var string
     */
    public $uploadDir = 'uploads/category/';

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * @var array
     */
    public $translatedAttributes = ['name'];

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany('App\Models\PackageType', 'parent_id');
    }

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\PackageType', 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function packages()
    {
        return $this->hasMany('App\Models\Package', 'type_id');
    }

    /**
     * @param $value
     * @return string
     */
    public function getIconAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : asset(config('ase.default.no-image'));
    }

    public function translateOrDefault($locale)
    {
        $tr = $this->getTranslation($locale, true);
        if (!$tr)
            $tr = $this->getTranslation('az', true);
        return $tr;
    }

}
