<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Cviebrock\EloquentSluggable\SluggableScopeHelpers;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\PageTranslation
 *
 * @property int $id
 * @property int $page_id
 * @property string $slug
 * @property string $locale
 * @property string $name
 * @property string|null $content
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $meta_keywords
 * @method static Builder|PageTranslation whereContent($value)
 * @method static Builder|PageTranslation whereId($value)
 * @method static Builder|PageTranslation whereLocale($value)
 * @method static Builder|PageTranslation whereMetaDescription($value)
 * @method static Builder|PageTranslation whereMetaKeywords($value)
 * @method static Builder|PageTranslation whereMetaTitle($value)
 * @method static Builder|PageTranslation whereName($value)
 * @method static Builder|PageTranslation wherePageId($value)
 * @method static Builder|PageTranslation whereSlug($value)
 * @mixin Eloquent
 * @property string $title
 * @method static Builder|PageTranslation findSimilarSlugs(Model $model, $attribute, $config, $slug)
 * @method static Builder|PageTranslation whereTitle($value)
 * @method static Builder|PageTranslation newModelQuery()
 * @method static Builder|PageTranslation newQuery()
 * @method static Builder|PageTranslation query()
 */
class PageTranslation extends MainTranslate
{
    use Sluggable;
    use SluggableScopeHelpers;

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'title',
            ],
        ];
    }
}
