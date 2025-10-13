<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Transaction
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $custom_id
 * @property string|null $paid_for
 * @property string|null $paid_by
 * @property string $type
 * @property float|null $amount
 * @property string|null $extra_data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Transaction newModelQuery()
 * @method static Builder|Transaction newQuery()
 * @method static Builder|Transaction query()
 * @method static Builder|Transaction whereAmount($value)
 * @method static Builder|Transaction whereCreatedAt($value)
 * @method static Builder|Transaction whereCustomId($value)
 * @method static Builder|Transaction whereExtraData($value)
 * @method static Builder|Transaction whereId($value)
 * @method static Builder|Transaction wherePaidBy($value)
 * @method static Builder|Transaction wherePaidFor($value)
 * @method static Builder|Transaction whereType($value)
 * @method static Builder|Transaction whereUpdatedAt($value)
 * @method static Builder|Transaction whereUserId($value)
 * @mixin Eloquent
 * @property int|null $admin_id
 * @property int|null $referral_id
 * @property string|null $note
 * @property-read Admin|null $admin
 * @property-read mixed $symbol_amount
 * @property-read User|null $user
 * @method static Builder|Transaction whereAdminId($value)
 * @method static Builder|Transaction whereNote($value)
 * @method static Builder|Transaction whereReferralId($value)
 */
class Transaction extends Model
{
    use ModelEventLogger;

    protected $fillable = [
        'paid_by',
        'user_id',
        'amount',
        'extra_data',
        'type',
        'custom_id',
        'phone',
        'paid_for',
        'referral_id',
        'request_all',
        'source_id',
        'admin_id',
        'debt',
        'city_id',
    ];

    protected $with = ['admin', 'user', 'package'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getCustomerAttribute()
    {
        if ($this->paid_for == 'TRACK_DEBT') {
            $track = $this->track;
            if ($track) {
                return $track->fullname;
            } else {
                return '';
            }
        } else {
            return '';
        }
    }

    public function getCourierNameAttribute()
    {
        $cd = $this->cd;
        if ($cd && $cd->courier)
            return $cd->courier->name;
        return '';
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function getWarehouseAttribute()
    {
        if ($this->paid_for == 'COURIER_DELIVERY') {
            return null;
        }
        if ($this->paid_for == 'TRACK_DELIVERY') {
            $track = $this->track;
            if ($track)
                return $track->warehouse;
        }
        $package = $this->package;
        if ($package)
            return $package->warehouse;
        return null;
    }


    public function getCwbAttribute()
    {
        if ($this->paid_for == 'COURIER_DELIVERY') {
            $cd = $this->cd;
            if ($cd)
                return $cd->packages_str;
        }
        if ($this->paid_for == 'TRACK_DELIVERY') {
            $track = $this->track;
            if ($track)
                return $track->tracking_code;
        }
        if ($this->paid_for == 'TRACK_DEBT') {
            $track = $this->track;
            if ($track)
                return $track->tracking_code;
        }
        if ($this->paid_for == 'TRACK_BROKER') {
            $track = $this->track;
            if ($track)
                return $track->tracking_code;
        }
        if ($this->paid_for == 'PACKAGE_BROKER') {
            $package = $this->package;
            if ($package)
                return $package->custom_id;
        }
        $package = $this->package;
        if ($package)
            return $package->custom_id;
        return '-';
    }

    public function getAwbAttribute()
    {
        $package = $this->package;
        if ($package)
            return $package->parcel_name;
        $cd = $this->cd;
        if ($cd)
            return '-';
        $track = $this->track;
        if ($track)
            return $track->container_name;
    }

    public function getAmount90Attribute()
    {
        $amount = $this->amount;
        if (!$amount) return '';
        return number_format(0 + round($amount * (0.9), 2), 2, ".", "");
    }

    public function getSymbolAmount90Attribute()
    {
        return (in_array($this->type, ['OUT', 'DEBT']) ? '-' : '+') . $this->amount_90 . " ₼";
    }

    public function cd()
    {
        return $this->belongsTo(CD::class, 'custom_id', 'id')
            ->withTrashed()
            ->when($this->paid_for === 'MARKET', function ($query) {
                $query->whereNull('id');
            });
    }


    public function package()
    {
//	if($this->paid_for == 'COURIER_DELIVERY')
//	    return NULL;
        return $this->belongsTo(Package::class, 'custom_id', 'id')->withTrashed();
    }

    public function track()
    {
        return $this->belongsTo(Track::class, 'custom_id', 'id')->withTrashed();
    }

    public function getSymbolAmountAttribute()
    {
        return (in_array($this->type, ['OUT', 'DEBT']) ? '-' : '+') . $this->amount . " ₼";
    }

    public static function addPackage($packageId, $type = 'CASH', $for = 'PACKAGE')
    {
        $package = Package::find($packageId);
        $deliveryManatPrice = $package->delivery_manat_price_discount;

        $user = User::find($package->user_id);
        //enum('GIFT_CARD','CASH','PAY_TR','MILLION','PORTMANAT','POST_TERMINAL','REFERRAL','CASHBACK','PACKAGE_BALANCE','ORDER_BALANCE','OTHER','REFUND')
        $check = Transaction::where('custom_id', $package->id)->where('paid_for', 'PACKAGE')->where('type', 'OUT')->first();
        if ($check && ($check->paid_by != 'PORTMANAT' || $check->type == 'ERROR')) {
            Transaction::where('custom_id', $package->id)->where('paid_for', 'PACKAGE')->delete();
        }
        if ($user && $package->delivery_price) {
            Transaction::create([
                'user_id' => $user->id,
                'custom_id' => $package->id,
                'paid_for' => $for,
                'type' => 'OUT',
                'paid_by' => $type,
                'amount' => $deliveryManatPrice,
            ]);
        }
    }

    public static function addCD($cdId, $type = 'CASH')
    {
        $cd = CD::withTrashed()->find($cdId);
        $user = User::find($cd->user_id);
        $for = 'COURIER_DELIVERY';
        $check = Transaction::where('custom_id', $cd->id)->where('paid_for', $for)->where('type', 'OUT')->first();
        if ($check) {
            if ($check->paid_by == 'PORTMANAT' && $check->type != 'ERROR' && $type == 'CASH') {
                return;
            }
            if ($check->paid_by != 'PORTMANAT' || $check->type == 'ERROR') {
                Transaction::where('custom_id', $cd->id)->where('paid_for', $for)->delete();
            }
        }
        if ($user && $cd->delivery_price) {
            Transaction::create([
                'user_id' => $user->id,
                'custom_id' => $cd->id,
                'paid_for' => $for,
                'type' => 'OUT',
                'paid_by' => $type,
                'amount' => $cd->delivery_price,
            ]);
        }
    }


    public static function addPackageFee($packageId, $fee = 3)
    {
        $package = Package::find($packageId);
        $user = User::find($package->user_id);

        if ($user) {
            Transaction::where('custom_id', $package->id)->where('paid_for', 'PACKAGE_SHIPPING')->delete();
            $pay_method_str = substr($fee, -1);
            $type = NULL;
            if (strtoupper($pay_method_str) == 'C' || strtoupper($pay_method_str) == 'С' || strtoupper($pay_method_str) == 'T' || strtoupper($pay_method_str) == 'Т') {
                $fee = substr($fee, 0, strlen($fee) - 1);
                if (strtoupper($pay_method_str) == 'C' || strtoupper($pay_method_str) == 'С')
                    $type = 'CASH';
                if (strtoupper($pay_method_str) == 'T' || strtoupper($pay_method_str) == 'Т')
                    $type = 'POST_TERMINAL';
            }
            $fee = floatval($fee);

            if (!$type) {
                $main = Transaction::where('custom_id', $package->id)->where('paid_for', 'PACKAGE')->where('type', 'OUT')->first();
                $type = ($main && $main->paid_by != 'PORTMANAT') ? $main->paid_by : 'CASH';
            }
            $check = Transaction::where('custom_id', $package->id)->where('paid_for', 'PACKAGE_SHIPPING')->where('type', 'OUT')->first();
            if (!$check && $fee && $fee != 0) {
                Transaction::create([
                    'user_id' => $user->id,
                    'custom_id' => $package->id,
                    'paid_for' => 'PACKAGE_SHIPPING',
                    'type' => 'OUT',
                    'paid_by' => $type,
                    'amount' => $fee,
                ]);
            } else {
                $fee = NULL;
            }
        }
        return $fee;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($query) {
            $query->admin_id = auth()->guard('admin')->check() ? auth()->guard('admin')->user()->id : null;

            if ($query->user_id) {
                $user = User::find($query->user_id);
                if ($user && $user->city_id) {
                    $query->city_id = $user->city_id;
                }
            }
        });
    }

    public function getRRNAttribute()
    {
        if (!$this->attributes['extra_data']) {
            return "-";
        }
        $pos = strpos($this->attributes['extra_data'], '<XMLOut>');
        if ($pos !== false) {
            return 'XMLOut';
        }
        $pos = strpos($this->attributes['extra_data'], '{');
        if ($pos === false || $pos != 0) {
            return $this->attributes['extra_data'];
        }

        $data = \GuzzleHttp\json_decode($this->attributes['extra_data'], true);

        return isset($data['body']['psp_rrn']) ? $data['body']['psp_rrn'] : "-";
    }


}
