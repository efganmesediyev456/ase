<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Country
 *
 * @property int $id
 * @property string $code
 * @property string $flag
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|CountryTranslation[] $translations
 * @method static Builder|Country listsTranslations($translationField)
 * @method static Builder|Country notTranslatedIn($locale = null)
 * @method static Builder|Country orWhereTranslation($key, $value, $locale = null)
 * @method static Builder|Country orWhereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Country translated()
 * @method static Builder|Country translatedIn($locale = null)
 * @method static Builder|Country whereCode($value)
 * @method static Builder|Country whereCreatedAt($value)
 * @method static Builder|Country whereFlag($value)
 * @method static Builder|Country whereId($value)
 * @method static Builder|Country whereTranslation($key, $value, $locale = null)
 * @method static Builder|Country whereTranslationLike($key, $value, $locale = null)
 * @method static Builder|Country whereUpdatedAt($value)
 * @method static Builder|Country withTranslation()
 * @mixin Eloquent
 * @property-read Collection|Country[] $warehouses
 * @property-read Warehouse $warehouse
 * @property-read Collection|Package[] $packages
 * @property int $delivery_index
 * @method static Builder|Country whereDeliveryIndex($value)
 * @property string|null $emails
 * @property int $status
 * @property-read int|null $packages_count
 * @property-read Collection|Page[] $pages
 * @property-read int|null $pages_count
 * @property-read int|null $translations_count
 * @property-read int|null $warehouses_count
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country query()
 * @method static Builder|Country whereEmails($value)
 * @method static Builder|Country whereStatus($value)
 */
class Country extends Model
{
    use Translatable;
    use Rememberable;

    public $uploadDir = 'uploads/countries/';
    /**
     * @var array
     */
    protected $with = ['translations'];

    /**
     * @var array
     */
    public $translatedAttributes = ['name','name1'];

    /**
     * @var array
     */
    protected $fillable = ['code'];

    /**
     * @return HasMany
     */
    public function warehouses()
    {
        return $this->hasMany('App\Models\Warehouse');
    }

    public function packages()
    {
        return $this->hasMany('App\Models\Package');
    }

    public function warehouse()
    {
        return $this->hasOne('App\Models\Warehouse')->whereNotIn('id',[17,18,19])->orderBy('id', 'desc');
    }

    public function pages()
    {
        return $this->belongsToMany('App\Models\Page', 'country_pages')->orderBy('order_num', 'desc');
    }

    /**
     * @param $value
     * @return string
     */
    public function getFlagAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : (file_exists(public_path('uploads/default/countries/' . $this->attributes['code'] . '.png')) ? asset('uploads/default/countries/' . $this->attributes['code'] . '.png') : asset(config('ase.default.no-image')));
    }
}
