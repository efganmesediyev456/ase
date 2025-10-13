<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\CityTranslation
 *
 * @property int $id
 * @property int $city_id
 * @property string $locale
 * @property string $name
 * @property string|null $address
 * @method static Builder|CityTranslation newModelQuery()
 * @method static Builder|CityTranslation newQuery()
 * @method static Builder|CityTranslation query()
 * @method static Builder|CityTranslation whereAddress($value)
 * @method static Builder|CityTranslation whereCityId($value)
 * @method static Builder|CityTranslation whereId($value)
 * @method static Builder|CityTranslation whereLocale($value)
 * @method static Builder|CityTranslation whereName($value)
 * @mixin Eloquent
 */
class CityTranslation extends MainTranslate
{
    use Rememberable;

    //
}
