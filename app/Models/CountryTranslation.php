<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\CountryTranslation
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $locale
 * @method static Builder|CountryTranslation whereCountryId($value)
 * @method static Builder|CountryTranslation whereId($value)
 * @method static Builder|CountryTranslation whereLocale($value)
 * @method static Builder|CountryTranslation whereName($value)
 * @mixin Eloquent
 * @method static Builder|CountryTranslation newModelQuery()
 * @method static Builder|CountryTranslation newQuery()
 * @method static Builder|CountryTranslation query()
 */
class CountryTranslation extends MainTranslate
{
    use Rememberable;

    protected $fillable = ['name'];
}
