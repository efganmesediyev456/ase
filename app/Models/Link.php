<?php

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Link
 *
 * @property int $id
 * @property int $order_id
 * @property string $url
 * @property string|null $note
 * @property string|null $affiliate
 * @property int $status
 * @property-read Order $order
 * @method static Builder|Link whereAffiliate($value)
 * @method static Builder|Link whereId($value)
 * @method static Builder|Link whereNote($value)
 * @method static Builder|Link whereOrderId($value)
 * @method static Builder|Link whereStatus($value)
 * @method static Builder|Link whereUrl($value)
 * @mixin Eloquent
 * @property-read mixed $status_with_label
 * @method static Builder|Link newModelQuery()
 * @method static Builder|Link newQuery()
 * @method static Builder|Link query()
 */
class Link extends Model
{
    protected $appends = ['status_with_label'];
    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @return BelongsTo
     */
    public function order()
    {
        return $this->belongsTo('App\Models\Order');
    }

    /**
     *
     */
    public function generateAffiliate()
    {
        //
    }

    public function getStatusWithLabelAttribute()
    {
        return config('ase.attributes.request.link.status')[$this->attributes['status']];
    }
}
