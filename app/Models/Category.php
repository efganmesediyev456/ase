<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Category
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string|null $icon
 * @property int $in_order
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Category[] $children
 * @property-read Collection|Coupon[] $coupons
 * @property-read Category|null $parent
 * @property-read Collection|Store[] $stores
 * @property-read Collection|CategoryTranslation[] $translations
 * @method static bool|null forceDelete()
 * @method static Builder|Category listsTranslations($translationField)
 * @method static Builder|Category notTranslatedIn($locale = null)
 * @method static Builder|Category onlyMain()
 * @method static Builder|Category onlyModels()
 * @method static \Illuminate\Database\Query\Builder|Category onlyTrashed()
 * @method static Builder|Category orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Category orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Category pluckMain()
 * @method static bool|null restore()
 * @method static Builder|Category translated()
 * @method static Builder|Category translatedIn($locale = null)
 * @method static Builder|Category whereCreatedAt($value)
 * @method static Builder|Category whereDeletedAt($value)
 * @method static Builder|Category whereIcon($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereInOrder($value)
 * @method static Builder|Category whereParentId($value)
 * @method static Builder|Category whereTranslation($key, $value, $locale = null)
 * @method static Builder|Category whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Category whereUpdatedAt($value)
 * @method static Builder|Category withTranslation()
 * @method static \Illuminate\Database\Query\Builder|Category withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Category withoutTrashed()
 * @mixin Eloquent
 * @property-read int|null $children_count
 * @property-read int|null $coupons_count
 * @property-read Collection|Product[] $products
 * @property-read int|null $products_count
 * @property-read int|null $stores_count
 * @property-read int|null $translations_count
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category query()
 */
class Category extends Model
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
    protected $with = ['translations'];

    /**
     * @var array
     */
    public $dates = ['deleted_at'];

    /**
     * @var array
     */
    public $translatedAttributes = ['name', 'description', 'slug', 'meta_title', 'meta_description', 'meta_keywords'];

    /**
     * @var array
     */
    protected $fillable = ['icon'];

    /**
     * @return BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo('App\Models\Category', 'parent_id');
    }

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany('App\Models\Category', 'parent_id');
    }

    /**
     * @return BelongsToMany
     */
    public function stores()
    {
        return $this->belongsToMany('App\Models\Store', 'store_categories');
    }

    public function products()
    {
        return $this->belongsToMany('App\Models\Product', 'product_categories');
    }

    /**
     * @return BelongsToMany
     */
    public function coupons()
    {
        return $this->belongsToMany('App\Models\Coupon', 'coupon_categories');
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeOnlyMain($query)
    {
        return $query->whereNull('parent_id')->remember(3000);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeOnlyModels($query)
    {
        return $query->with('parent')->whereNotNull('parent_id')->orderBy('parent_id', 'ASC')->remember(40000);
    }

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopePluckMain($query)
    {
        return $query->onlyMain()->pluck('name', 'id')->all();
    }

    /**
     * @param $value
     * @return string
     */
    public function getIconAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : asset(config('ase.default.no-image'));
    }
}
