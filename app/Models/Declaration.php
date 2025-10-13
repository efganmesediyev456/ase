<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Declaration
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $type_id
 * @property string|null $tracking_code
 * @property int|null $number_items
 * @property float|null $shipping_amount
 * @property string|null $comment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PackageType|null $type
 * @property-read User $user
 * @method static Builder|Declaration whereComment($value)
 * @method static Builder|Declaration whereCreatedAt($value)
 * @method static Builder|Declaration whereId($value)
 * @method static Builder|Declaration whereNumberItems($value)
 * @method static Builder|Declaration whereShippingAmount($value)
 * @method static Builder|Declaration whereTrackingCode($value)
 * @method static Builder|Declaration whereTypeId($value)
 * @method static Builder|Declaration whereUpdatedAt($value)
 * @method static Builder|Declaration whereUserId($value)
 * @mixin Eloquent
 * @method static Builder|Declaration newModelQuery()
 * @method static Builder|Declaration newQuery()
 * @method static Builder|Declaration query()
 */
class Declaration extends Model
{
    /**
     * @var array
     */
    public $with = ['user', 'type'];

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\Models\PackageType', 'type_id');
    }
}
