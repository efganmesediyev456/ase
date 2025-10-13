<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Coupon
 *
 * @property int $id
 * @property int|null $store_id
 * @property int|null $type_id
 * @property string|null $url
 * @property string|null $code
 * @property string|null $image
 * @property int $featured
 * @property string|null $start_at
 * @property string|null $end_at
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Category[] $categories
 * @property-read Store|null $store
 * @property-read Collection|CouponTranslation[] $translations
 * @method static Builder|Coupon listsTranslations($translationField)
 * @method static Builder|Coupon notTranslatedIn($locale = null)
 * @method static Builder|Coupon orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Coupon orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Coupon translated()
 * @method static Builder|Coupon translatedIn($locale = null)
 * @method static Builder|Coupon whereCode($value)
 * @method static Builder|Coupon whereCreatedAt($value)
 * @method static Builder|Coupon whereDeletedAt($value)
 * @method static Builder|Coupon whereEndAt($value)
 * @method static Builder|Coupon whereFeatured($value)
 * @method static Builder|Coupon whereId($value)
 * @method static Builder|Coupon whereImage($value)
 * @method static Builder|Coupon whereStartAt($value)
 * @method static Builder|Coupon whereStoreId($value)
 * @method static Builder|Coupon whereTranslation($key, $value, $locale = null)
 * @method static Builder|Coupon whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Coupon whereTypeId($value)
 * @method static Builder|Coupon whereUpdatedAt($value)
 * @method static Builder|Coupon whereUrl($value)
 * @method static Builder|Coupon withTranslation()
 * @mixin Eloquent
 * @method static Builder|Coupon active()
 * @property-read int|null $categories_count
 * @property-read int|null $translations_count
 * @method static Builder|Coupon newModelQuery()
 * @method static Builder|Coupon newQuery()
 * @method static Builder|Coupon query()
 */
class Coupon extends Model
{
    use Translatable;
    use Rememberable;

    /**
     * @var string
     */
    public $uploadDir = 'uploads/coupons/';

    /**
     * @var array
     */
    public $translatedAttributes = ['name', 'description'];

    /**
     * @var array
     */
    public $with = ['store', 'translations', 'categories'];

    /**
     * @return mixed
     */
    public function store()
    {
        return $this->belongsTo('App\Models\Store')->withTrashed();
    }

    /**
     * @return BelongsToMany
     */
    public function categories()
    {
        return $this->belongsToMany('App\Models\Category', 'coupon_categories');
    }

    /**
     * @param $value
     * @return string
     */
    public function getImageAttribute($value)
    {
        return $value ? (str_contains($value, '//') ? $value : asset($this->uploadDir . $value)) : asset(config('ase.default.no-image'));
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_at')->orWhere('end_at', '>=', Carbon::now());
    }
}
