<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\SMSTemplate
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property string $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|SMSTemplate onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|SMSTemplate whereContent($value)
 * @method static Builder|SMSTemplate whereCreatedAt($value)
 * @method static Builder|SMSTemplate whereDeletedAt($value)
 * @method static Builder|SMSTemplate whereId($value)
 * @method static Builder|SMSTemplate whereKey($value)
 * @method static Builder|SMSTemplate whereName($value)
 * @method static Builder|SMSTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|SMSTemplate withTrashed()
 * @method static \Illuminate\Database\Query\Builder|SMSTemplate withoutTrashed()
 * @mixin Eloquent
 * @property int $active
 * @method static Builder|SMSTemplate newModelQuery()
 * @method static Builder|SMSTemplate newQuery()
 * @method static Builder|SMSTemplate query()
 * @method static Builder|SMSTemplate whereActive($value)
 */
class SMSTemplate extends Model
{
    use SoftDeletes;

    protected $table = "s_m_s_templates";
    protected $fillable = [
        "key", "name",
        "content",
    ];
}
