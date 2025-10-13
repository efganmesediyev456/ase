<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property string $custom_id
 * @property string|null $note
 * @property string|null $extra_contacts
 * @property string|null $admin_note
 * @property string|null $affiliate
 * @property string|null $price
 * @property string|null $service_fee
 * @property string|null $coupon_sale
 * @property string|null $total_price
 * @property int|null $package_id
 * @property int|null $country_id
 * @property int $status
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 * @property-read Collection|Link[] $links
 * @property-read Package|null $package
 * @property-read User $user
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Order onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|Order whereAdminNote($value)
 * @method static Builder|Order whereAffiliate($value)
 * @method static Builder|Order whereCountryId($value)
 * @method static Builder|Order whereCouponSale($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereCustomId($value)
 * @method static Builder|Order whereDeletedAt($value)
 * @method static Builder|Order whereExtraContacts($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order wherePackageId($value)
 * @method static Builder|Order wherePrice($value)
 * @method static Builder|Order whereServiceFee($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereTotalPrice($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Order withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Order withoutTrashed()
 * @mixin Eloquent
 * @property-read mixed $status_info
 * @property-read int|null $links_count
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 */
class Order extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    /**
     * @var array
     */
    public $with = ['user', 'country'];

    /**
     * @var array
     */
    public $dates = ['deleted_at'];

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
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * @return BelongsTo
     */
    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    /**
     * @return HasMany
     */
    public function links()
    {
        return $this->hasMany('App\Models\Link');
    }

    public function getStatusInfoAttribute()
    {
        $labels = ['warning', 'info', 'primary', 'success', 'danger', 'success'];
        return [
            'text' => trans(config('ase.attributes.request.statusTrans')[$this->attributes['status']]),
            'label' => $labels[$this->attributes['status']]
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($query) {

            if (auth()->guard('admin')->check()) {
                $query->admin_id = auth()->guard('admin')->user()->id;
            }
        });

        static::creating(function ($query) {
        });
    }
}
