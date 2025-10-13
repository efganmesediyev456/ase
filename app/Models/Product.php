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
 * App\Models\Product
 *
 * @property int $id
 * @property int|null $store_id
 * @property string|null $url
 * @property string|null $old_price
 * @property string|null $price
 * @property string|null $product
 * @property string|null $image
 * @property int $featured
 * @property string|null $start_at
 * @property string|null $end_at
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Category[] $categories
 * @property-read Store|null $store
 * @property-read Collection|ProductTranslation[] $translations
 * @method static Builder|Product listsTranslations($translationField)
 * @method static Builder|Product notTranslatedIn($locale = null)
 * @method static Builder|Product orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Product orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Product translated()
 * @method static Builder|Product translatedIn($locale = null)
 * @method static Builder|Product whereCreatedAt($value)
 * @method static Builder|Product whereDeletedAt($value)
 * @method static Builder|Product whereEndAt($value)
 * @method static Builder|Product whereFeatured($value)
 * @method static Builder|Product whereId($value)
 * @method static Builder|Product whereImage($value)
 * @method static Builder|Product whereOldPrice($value)
 * @method static Builder|Product wherePrice($value)
 * @method static Builder|Product whereProduct($value)
 * @method static Builder|Product whereStartAt($value)
 * @method static Builder|Product whereStoreId($value)
 * @method static Builder|Product whereTranslation($key, $value, $locale = null)
 * @method static Builder|Product whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Product whereUpdatedAt($value)
 * @method static Builder|Product whereUrl($value)
 * @method static Builder|Product withTranslation()
 * @mixin Eloquent
 * @property string|null $sale
 * @method static Builder|Product whereSale($value)
 * @property-read int|null $categories_count
 * @property-read int|null $translations_count
 * @method static Builder|Product newModelQuery()
 * @method static Builder|Product newQuery()
 * @method static Builder|Product query()
 */
class Product extends Model
{
    use Translatable;
    use Rememberable;

    public $uploadDir = 'uploads/products/';

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
        return $this->belongsToMany('App\Models\Category', 'product_categories');
    }

    public function getImageAttribute($value)
    {
        return $value ? (str_contains($value, '//') ? $value : asset($this->uploadDir . $value)) : asset(config('ase.default.no-image'));
    }
}
