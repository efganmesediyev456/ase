<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\NotificationQueue
 *
 * @method static Builder|NotificationQueue newModelQuery()
 * @method static Builder|NotificationQueue newQuery()
 * @method static Builder|NotificationQueue query()
 * @mixin Eloquent
 * @property int $id
 * @property string $type
 * @property string|null $to
 * @property string|null $extra_to
 * @property string|null $subject
 * @property string|null $content
 * @property int $sent
 * @property string|null $error_message
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|NotificationQueue whereContent($value)
 * @method static Builder|NotificationQueue whereCreatedAt($value)
 * @method static Builder|NotificationQueue whereErrorMessage($value)
 * @method static Builder|NotificationQueue whereExtraTo($value)
 * @method static Builder|NotificationQueue whereId($value)
 * @method static Builder|NotificationQueue whereSent($value)
 * @method static Builder|NotificationQueue whereSubject($value)
 * @method static Builder|NotificationQueue whereTo($value)
 * @method static Builder|NotificationQueue whereType($value)
 * @method static Builder|NotificationQueue whereUpdatedAt($value)
 * @property string $send_for
 * @property int|null $send_for_id
 * @method static Builder|NotificationQueue whereSendFor($value)
 * @method static Builder|NotificationQueue whereSendForId($value)
 */
class NotificationQueue extends Model
{
    protected $fillable = ['user_id','type', 'to', 'from', 'extra_to', 'subject', 'content', 'sent', 'error_message', 'send_for', 'send_for_id','scheduled_at'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function getMessageAttribute()
    {
        if($this->attributes['type'] == 'WHATSAPP'){
            $content = json_decode($this->attributes['content'],true);
            if(isset($content["whatsapp"])){
                return $content["whatsapp"];
            }
        }

        return $this->attributes['content'];

    }
}
