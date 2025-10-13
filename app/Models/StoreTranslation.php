<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\StoreTranslation
 *
 * @property int $id
 * @property int $store_id
 * @property string $slug
 * @property string $locale
 * @property string $name
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @method static Builder|StoreTranslation findSimilarSlugs(Model $model, $attribute, $config, $slug)
 * @method static Builder|StoreTranslation whereDescription($value)
 * @method static Builder|StoreTranslation whereId($value)
 * @method static Builder|StoreTranslation whereLocale($value)
 * @method static Builder|StoreTranslation whereMetaDescription($value)
 * @method static Builder|StoreTranslation whereMetaKeywords($value)
 * @method static Builder|StoreTranslation whereMetaTitle($value)
 * @method static Builder|StoreTranslation whereName($value)
 * @method static Builder|StoreTranslation whereSlug($value)
 * @method static Builder|StoreTranslation whereStoreId($value)
 * @mixin Eloquent
 * @method static Builder|StoreTranslation newModelQuery()
 * @method static Builder|StoreTranslation newQuery()
 * @method static Builder|StoreTranslation query()
 */
class StoreTranslation extends MainTranslate
{
    //
}
