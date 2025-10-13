<?php

namespace App\Models;

use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * App\Models\PackageGood
 *
 * @property int $id
 * @property int $package_id
 * @property int $code
 * @property-read Package $package
 * @method static Builder|PackageCarrier withTrashed()
 * @method static Builder|PackageCarrier withoutTrashed()
 * @mixin Eloquent
 */
class PackageGood extends Model
{
    use SoftDeletes;
    //use ModelEventLogger;
    protected $table = 'package_goods';

    protected $fillable = [
        'package_id',
        'track_id',
        'type_id',
        'customs_type_id',
        'customs_type_parent_id',
        'country_id',
        'warehouse_id',
        'ru_type_id',
        'name',
        'number_items',
        'shipping_amount_cur',
        'shipping_amount',
        'shipping_amount_usd',
        'shipping_amount_rub',
        'shipping_amount_azn',
        'weight',
    ];

    public function getNameParentAttribute()
    {
        if ($this->ru_type) {
            return $this->ru_type->name_ru;
        }
        if ($this->customs_type) {
            return $this->customs_type->name_en_parent;
        }
        if ($this->type) {
            return $this->type->translateOrDefault('en')->name;
        }
        return '-';
    }

    public function getNameOneAttribute()
    {
        if ($this->ru_type) {
            return $this->ru_type->name_ru;
        }
        if ($this->customs_type) {
            return $this->customs_type->name_en_one;
        }
        if ($this->type) {
            return $this->type->translateOrDefault('en')->name;
        }
        return '-';
    }

    public function getNameAttribute()
    {
        if ($this->ru_type) {
            return $this->ru_type->name_ru;
        }
        if ($this->customs_type) {
            return $this->customs_type->name_en_with_parent;
        }
        if ($this->type) {
            return $this->type->translateOrDefault('en')->name;
        }
        return '-';
    }

    public function type()
    {
        return $this->belongsTo('App\Models\PackageType', 'type_id')->withTrashed();
    }

    public function customs_type()
    {
        return $this->belongsTo('App\Models\CustomsType', 'customs_type_id');
    }

    public function ru_type()
    {
        return $this->belongsTo('App\Models\RuType', 'ru_type_id');
    }


    public function package()
    {
        return $this->hasOne('App\Models\Package', 'package_id');
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }

    public function defaultCountry()
    {
        return ($this->warehouse and $this->warehouse->country) ? $this->warehouse->country : ($this->country ? $this->country : null);
    }

    public function getShippingOrgPriceAttribute()
    {
        return $this->shipping_amount ? ($this->shipping_amount . " " . config('ase.attributes.currencies')[$this->attributes['shipping_amount_cur']]) : '-';
    }

    public function getShippingPriceAttribute()
    {
        $country = $this->defaultCountry();

        return $this->shipping_amount ? ($country && $country->convert_invoice_to_usd ? ($this->getShippingAmountUSD() . " USD") : ($this->shipping_amount . " " . config('ase.attributes.currencies')[$this->attributes['shipping_amount_cur']])) : "-";
    }

    public function getShippingPriceRuAttribute()
    {
        $country = $this->defaultCountry();

        return $this->shipping_amount ? ($this->getShippingAmountRUB() . " RUB") : "-";
    }

    public function getShippingAmountUSD()
    {
        $shippingAmountUSD = $this->shipping_amount_usd;
        if (!empty($shippingAmountUSD))
            return $shippingAmountUSD;

        $shippingAmount = $this->shipping_amount;
        if (empty($shippingAmount))
            return $shippingAmount;
        $shippingAmountUSD = number_format(0 + round($this->shipping_amount * 1 / getCurrencyRate($this->attributes['shipping_amount_cur']), 2), 2, ".", "");

        $ldate = date('Y-m-d H:i:s');
        $str = "update package_goods set shipping_amount_usd=?,shipping_amount_usd_at=? where id=?";
        DB::update($str, [$shippingAmountUSD, $ldate, $this->id]);
        $this->shipping_amount_usd = $shippingAmountUSD;

        return $shippingAmountUSD;
    }

    public function getShippingAmountRUB()
    {
        $shippingAmountRUB = $this->shipping_amount_rub;
        if (!empty($shippingAmountRUB))
            return $shippingAmountRUB;

        $shippingAmount = $this->shipping_amount;
        if (empty($shippingAmount))
            return $shippingAmount;
        if ($this->attributes['shipping_amount_cur'] == 4)
            $shippingAmountRUB = number_format(0 + round($this->shipping_amount, 2), 2, ".", "");
        else
            $shippingAmountRUB = number_format(0 + round($this->shipping_amount * getCurrencyRate(4) / getCurrencyRate($this->attributes['shipping_amount_cur']), 2), 2, ".", "");

        $ldate = date('Y-m-d H:i:s');
        $str = "update package_goods set shipping_amount_rub=?,shipping_amount_rub_at=? where id=?";
        DB::update($str, [$shippingAmountRUB, $ldate, $this->id]);
        $this->shipping_amount_rub = $shippingAmountRUB;

        return $shippingAmountRUB;
    }
}
