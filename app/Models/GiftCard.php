<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

/**
 * App\Models\GiftCard
 *
 * @property int $id
 * @property int|null $user_id
 * @property string $card_number
 * @property float|null $amount
 * @property string $status
 * @property string|null $used_at
 * @property string|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard newQuery()
 * @method static Builder|GiftCard onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard query()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|GiftCard whereUserId($value)
 * @method static Builder|GiftCard withTrashed()
 * @method static Builder|GiftCard withoutTrashed()
 * @mixin Eloquent
 */
class GiftCard extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCardNumber()
    {
        $latest = (self::latest()->first());

        if (!$latest) {
            return '100000001';
        }

        $latest = $latest->card_number;
        do {
            $latest++;

            $code = $latest;
            $check = self::where('card_number', $code)->first();
            if (!$check) {
                break;
            }
        } while (true);

        return $code;
    }

    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {
            $query->card_number = $query->card_number ?: self::generateCardNumber();
        });
    }
}
