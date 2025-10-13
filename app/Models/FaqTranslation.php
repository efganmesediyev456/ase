<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;

/**
 * App\Models\FaqTranslation
 *
 * @property int $id
 * @property int $faq_id
 * @property string $locale
 * @property string|null $question
 * @property string|null $answer
 * @method static Builder|FaqTranslation whereAnswer($value)
 * @method static Builder|FaqTranslation whereFaqId($value)
 * @method static Builder|FaqTranslation whereId($value)
 * @method static Builder|FaqTranslation whereLocale($value)
 * @method static Builder|FaqTranslation whereQuestion($value)
 * @mixin Eloquent
 * @method static Builder|FaqTranslation newModelQuery()
 * @method static Builder|FaqTranslation newQuery()
 * @method static Builder|FaqTranslation query()
 */
class FaqTranslation extends MainTranslate
{
    //
}
