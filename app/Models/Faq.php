<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Faq
 *
 * @property int $id
 * @property int $in_order
 * @property int $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|FaqTranslation[] $translations
 * @method static Builder|Faq listsTranslations($translationField)
 * @method static Builder|Faq notTranslatedIn($locale = null)
 * @method static Builder|Faq orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Faq orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Faq translated()
 * @method static Builder|Faq translatedIn($locale = null)
 * @method static Builder|Faq whereCreatedAt($value)
 * @method static Builder|Faq whereId($value)
 * @method static Builder|Faq whereInOrder($value)
 * @method static Builder|Faq whereStatus($value)
 * @method static Builder|Faq whereTranslation($key, $value, $locale = null)
 * @method static Builder|Faq whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Faq whereUpdatedAt($value)
 * @method static Builder|Faq withTranslation()
 * @mixin Eloquent
 * @property-read int|null $translations_count
 * @method static Builder|Faq newModelQuery()
 * @method static Builder|Faq newQuery()
 * @method static Builder|Faq query()
 */
class Faq extends Model
{
    use Translatable;
    use Rememberable;

    /**
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @var bool
     */
    public $useTranslationFallback = true;

    /**
     * @var array
     */
    public $translatedAttributes = ['question', 'answer'];
}
