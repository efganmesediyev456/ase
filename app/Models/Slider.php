<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Slider
 *
 * @property int $id
 * @property string|null $name
 * @property string $image
 * @property string|null $url
 * @property int $target_black
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Slider whereCreatedAt($value)
 * @method static Builder|Slider whereId($value)
 * @method static Builder|Slider whereImage($value)
 * @method static Builder|Slider whereName($value)
 * @method static Builder|Slider whereTargetBlack($value)
 * @method static Builder|Slider whereUpdatedAt($value)
 * @method static Builder|Slider whereUrl($value)
 * @mixin Eloquent
 * @property-read Collection|SliderTranslation[] $translations
 * @method static Builder|Slider listsTranslations($translationField)
 * @method static Builder|Slider notTranslatedIn($locale = null)
 * @method static Builder|Slider orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Slider orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Slider translated()
 * @method static Builder|Slider translatedIn($locale = null)
 * @method static Builder|Slider whereTranslation($key, $value, $locale = null)
 * @method static Builder|Slider whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Slider withTranslation()
 * @property-read int|null $translations_count
 * @method static Builder|Slider newModelQuery()
 * @method static Builder|Slider newQuery()
 * @method static Builder|Slider query()
 */
class Slider extends Model
{
    use Translatable;

    public $uploadDir = 'uploads/slider/';

    public $translatedAttributes = ['title', 'content', 'button_label'];

    public function getImageAttribute($value)
    {
        return $value ? (str_contains($value, '//') ? $value : asset($this->uploadDir . $value)) : asset('front/images/slider.jpg');
    }
}
