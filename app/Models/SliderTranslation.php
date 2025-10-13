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
 * @property int $slider_id
 * @property string|null $title
 * @property string|null $content
 * @property string|null $button_label
 * @method static Builder|SliderTranslation whereButtonLabel($value)
 * @method static Builder|SliderTranslation whereContent($value)
 * @method static Builder|SliderTranslation whereSliderId($value)
 * @method static Builder|SliderTranslation whereTitle($value)
 * @method static Builder|SliderTranslation newModelQuery()
 * @method static Builder|SliderTranslation newQuery()
 * @method static Builder|SliderTranslation query()
 */
class SliderTranslation extends MainTranslate
{
    //
}
