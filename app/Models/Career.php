<?php

namespace App\Models;

use Carbon\Carbon;
use Dimsav\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Rememberable\Rememberable;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Career
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @property-read Collection|CareerTranslation[] $translations
 */
class Career extends Model
{
    use Translatable, Rememberable, SoftDeletes;

    protected $with = ['translations'];

    protected $fillable = ['is_active'];

    public $translatedAttributes = ['name', 'city'];
}
