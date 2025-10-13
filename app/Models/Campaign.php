<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\Campaign
 *
 * @property int $id
 * @property string|null $title
 * @property int $send_after
 * @property string|null $content
 * @property string|null $filtering
 * @property string|null $users
 * @property string|null $excluded_users
 * @property string $type
 * @property string|null $response_data
 * @property int $sent
 * @property int $matched
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign newQuery()
 * @method static Builder|Campaign onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereExcludedUsers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereFiltering($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereMatched($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereResponseData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereSendAfter($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Campaign whereUsers($value)
 * @method static Builder|Campaign withTrashed()
 * @method static Builder|Campaign withoutTrashed()
 * @mixin Eloquent
 * @property-read mixed $no_action
 */
class Campaign extends Model
{
    use SoftDeletes;

    public function getNoActionAttribute()
    {
        return boolval($this->attributes['sent']);
    }


    public function getSentStatusAttribute()
    {
        return $this->sent ? "Yes" : "No";
    }
}
