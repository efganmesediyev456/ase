<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\Page
 *
 * @property int $id
 * @property int $type
 * @property string|null $image
 * @property string|null $keyword
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|PageTranslation[] $translations
 * @method static Builder|Page listsTranslations($translationField)
 * @method static Builder|Page news()
 * @method static Builder|Page notTranslatedIn($locale = null)
 * @method static Builder|Page orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Page orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Page self()
 * @method static Builder|Page translated()
 * @method static Builder|Page translatedIn($locale = null)
 * @method static Builder|Page whereCreatedAt($value)
 * @method static Builder|Page whereId($value)
 * @method static Builder|Page whereImage($value)
 * @method static Builder|Page whereKeyword($value)
 * @method static Builder|Page whereStatus($value)
 * @method static Builder|Page whereTranslation($key, $value, $locale = null)
 * @method static Builder|Page whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Page whereType($value)
 * @method static Builder|Page whereUpdatedAt($value)
 * @method static Builder|Page withTranslation()
 * @mixin Eloquent
 * @property Carbon|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Page onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|Page whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Page withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Page withoutTrashed()
 * @property-read int|null $translations_count
 * @method static Builder|Page newModelQuery()
 * @method static Builder|Page newQuery()
 * @method static Builder|Page query()
 */
class Page extends Model
{
    use Translatable;
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $with = ['translations'];

    public $uploadDir = 'uploads/news/';

    /**
     * @var array
     */
    public $translatedAttributes = ['title', 'content', 'slug', 'meta_title', 'meta_description', 'meta_keywords'];

    /**
     * @param $query
     * @return mixed
     */
    public function scopeNews($query)
    {
        return $query->where('type', 1);
    }

    /**
     * @param $query
     * @return mixed
     */
    public function scopeSelf($query)
    {
        return $query->where('type', 0);
    }

    public function getImageAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : asset(config('ase.default.no-image'));
    }
}
