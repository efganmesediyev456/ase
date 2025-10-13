<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CouponTranslation
 *
 * @property int $id
 * @property int $coupon_id
 * @property string $slug
 * @property string $locale
 * @property string $name
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @method static Builder|CouponTranslation findSimilarSlugs(Model $model, $attribute, $config, $slug)
 * @method static Builder|CouponTranslation whereCouponId($value)
 * @method static Builder|CouponTranslation whereDescription($value)
 * @method static Builder|CouponTranslation whereId($value)
 * @method static Builder|CouponTranslation whereLocale($value)
 * @method static Builder|CouponTranslation whereMetaDescription($value)
 * @method static Builder|CouponTranslation whereMetaKeywords($value)
 * @method static Builder|CouponTranslation whereMetaTitle($value)
 * @method static Builder|CouponTranslation whereName($value)
 * @method static Builder|CouponTranslation whereSlug($value)
 * @mixin Eloquent
 * @method static Builder|CouponTranslation newModelQuery()
 * @method static Builder|CouponTranslation newQuery()
 * @method static Builder|CouponTranslation query()
 */
class CouponTranslation extends MainTranslate
{
    //
}
