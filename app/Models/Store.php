<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Store
 *
 * @property int $id
 * @property string $url
 * @property string|null $logo
 * @property int $featured
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Category[] $categories
 * @property-read Collection|Coupon[] $coupons
 * @property-read Collection|StoreTranslation[] $translations
 * @method static bool|null forceDelete()
 * @method static Builder|Store listsTranslations($translationField)
 * @method static Builder|Store notTranslatedIn($locale = null)
 * @method static \Illuminate\Database\Query\Builder|Store onlyTrashed()
 * @method static Builder|Store orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Store orWhereTranslationLike($key, $value, $locale = null)
 * @method static bool|null restore()
 * @method static Builder|Store translated()
 * @method static Builder|Store translatedIn($locale = null)
 * @method static Builder|Store whereCreatedAt($value)
 * @method static Builder|Store whereDeletedAt($value)
 * @method static Builder|Store whereFeatured($value)
 * @method static Builder|Store whereId($value)
 * @method static Builder|Store whereLogo($value)
 * @method static Builder|Store whereTranslation($key, $value, $locale = null)
 * @method static Builder|Store whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Store whereUpdatedAt($value)
 * @method static Builder|Store whereUrl($value)
 * @method static Builder|Store withTranslation()
 * @method static \Illuminate\Database\Query\Builder|Store withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Store withoutTrashed()
 * @mixin Eloquent
 * @property float|null $sale
 * @method static Builder|Store featured()
 * @method static Builder|Store whereSale($value)
 * @property-read int|null $categories_count
 * @property-read int|null $coupons_count
 * @property-read int|null $translations_count
 * @method static Builder|Store newModelQuery()
 * @method static Builder|Store newQuery()
 * @method static Builder|Store query()
 */
class Store extends Model
{
    use Translatable;
    use Rememberable;
    use SoftDeletes;

    /**
     * @var array
     */
    protected $with = ['country', 'translations'];

    /**
     * @var string
     */
    public $uploadDir = 'uploads/stores/';

    /**
     * @var array
     */
    public $translatedAttributes = ['name', 'description'];

    /**
     * Categories
     *
     * @return BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Category', 'store_categories');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * Coupons related with this store
     *
     * @return HasMany
     */
    public function coupons()
    {
        return $this->hasMany('App\Models\Coupon');
    }

    /**
     * Logo attribute
     *
     * @param $value
     * @return string
     */
    public function getLogoAttribute($value)
    {
        return $value ? (str_contains($value, '//') ? $value : asset($this->uploadDir . $value)) : asset(config('ase.default.no-image'));
    }

    public function getSaleAttribute($value)
    {
        return $value + 0;
    }

    public function scopeFeatured($query)
    {
        return $query->whereFeatured(true);
    }

    public function getCashbackLinkAttribute()
    {
        //return "http://aseshop.link/r?url=" . $this->attributes['url'];
        return "" . $this->attributes['url'];
    }
}
