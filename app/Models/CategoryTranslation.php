<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CategoryTranslation
 *
 * @property int $id
 * @property int $category_id
 * @property string $slug
 * @property string $locale
 * @property string $name
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @method static Builder|CategoryTranslation findSimilarSlugs(Model $model, $attribute, $config, $slug)
 * @method static Builder|CategoryTranslation whereCategoryId($value)
 * @method static Builder|CategoryTranslation whereDescription($value)
 * @method static Builder|CategoryTranslation whereId($value)
 * @method static Builder|CategoryTranslation whereLocale($value)
 * @method static Builder|CategoryTranslation whereMetaDescription($value)
 * @method static Builder|CategoryTranslation whereMetaKeywords($value)
 * @method static Builder|CategoryTranslation whereMetaTitle($value)
 * @method static Builder|CategoryTranslation whereName($value)
 * @method static Builder|CategoryTranslation whereSlug($value)
 * @mixin Eloquent
 * @method static Builder|CategoryTranslation newModelQuery()
 * @method static Builder|CategoryTranslation newQuery()
 * @method static Builder|CategoryTranslation query()
 */
class CategoryTranslation extends MainTranslate
{
    use Sluggable;

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name',
            ],
        ];
    }

    protected $fillable = ['name', 'description'];
}
