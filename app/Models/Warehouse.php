<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use App\Traits\Password;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Warehouse
 *
 * @property int $id
 * @property string|null $company_name
 * @property string|null $contact_name
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property int|null $country_id
 * @property string $phone
 * @property string|null $mobile
 * @property string $city
 * @property string|null $state
 * @property string|null $region
 * @property string $zip_code
 * @property string|null $passport
 * @property string|null $attention
 * @property string|null $reminder
 * @property float $half_kg
 * @property float $per_kg
 * @property float|null $width
 * @property float|null $height
 * @property float|null $length
 * @property int $currency
 * @property int $per_week
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 * @property-read mixed $currency_with_label
 * @property-read Collection|Package[] $packages
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Warehouse onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|Warehouse whereAddressLine1($value)
 * @method static Builder|Warehouse whereAddressLine2($value)
 * @method static Builder|Warehouse whereAttention($value)
 * @method static Builder|Warehouse whereCity($value)
 * @method static Builder|Warehouse whereCompanyName($value)
 * @method static Builder|Warehouse whereContactName($value)
 * @method static Builder|Warehouse whereCountryId($value)
 * @method static Builder|Warehouse whereCreatedAt($value)
 * @method static Builder|Warehouse whereCurrency($value)
 * @method static Builder|Warehouse whereDeletedAt($value)
 * @method static Builder|Warehouse whereEmail($value)
 * @method static Builder|Warehouse whereHalfKg($value)
 * @method static Builder|Warehouse whereHeight($value)
 * @method static Builder|Warehouse whereId($value)
 * @method static Builder|Warehouse whereLength($value)
 * @method static Builder|Warehouse whereMobile($value)
 * @method static Builder|Warehouse wherePassport($value)
 * @method static Builder|Warehouse wherePassword($value)
 * @method static Builder|Warehouse wherePerKg($value)
 * @method static Builder|Warehouse wherePerWeek($value)
 * @method static Builder|Warehouse wherePhone($value)
 * @method static Builder|Warehouse whereRegion($value)
 * @method static Builder|Warehouse whereRememberToken($value)
 * @method static Builder|Warehouse whereReminder($value)
 * @method static Builder|Warehouse whereState($value)
 * @method static Builder|Warehouse whereUpdatedAt($value)
 * @method static Builder|Warehouse whereWidth($value)
 * @method static Builder|Warehouse whereZipCode($value)
 * @method static \Illuminate\Database\Query\Builder|Warehouse withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Warehouse withoutTrashed()
 * @mixin Eloquent
 * @property string|null $key
 * @method static Builder|Warehouse whereKey($value)
 * @property float|null $to_100g
 * @property float|null $from_100g_to_200g
 * @property float|null $from_200g_to_500g
 * @property float|null $from_500g_to_1kq
 * @property float $up_10_kg
 * @method static Builder|Warehouse whereFrom100gTo200g($value)
 * @method static Builder|Warehouse whereFrom200gTo500g($value)
 * @method static Builder|Warehouse whereFrom500gTo1kq($value)
 * @method static Builder|Warehouse whereTo100g($value)
 * @method static Builder|Warehouse whereUp10Kg($value)
 * @property float|null $per_g
 * @property int $parcelling
 * @property int $package_processing
 * @property-read mixed $flies_per_week
 * @property-read mixed $flies_week
 * @property-read int|null $packages_count
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse whereParcelling($value)
 * @method static Builder|Warehouse wherePerG($value)
 * @property int $auto_print
 * @property int $auto_print_pp
 * @property int $show_invoice
 * @property int $show_label
 * @property string|null $panel_login
 * @property string|null $panel_password
 * @property-read Address $address
 * @property-read Collection|Address[] $addresses
 * @property-read int|null $addresses_count
 * @method static Builder|Warehouse whereAutoPrint($value)
 * @method static Builder|Warehouse wherePanelLogin($value)
 * @method static Builder|Warehouse wherePanelPassword($value)
 * @method static Builder|Warehouse whereShowInvoice($value)
 * @method static Builder|Warehouse whereShowLabel($value)
 * @property int $allow_make_fake_invoice
 * @property int $check_carriers
 * @method static Builder|Warehouse whereAllowMakeFakeInvoice($value)
 */
class Warehouse extends Authenticatable
{
    use Rememberable;
    use SoftDeletes;
    use Password;
    use ModelEventLogger;

    /**
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * @var array
     */
    protected $appends = ['currency_with_label'];

    /**
     * @var array
     */
    public $with = ['country', 'addresses'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return BelongsTo
     */
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    /**
     * @return HasMany
     */
    public function packages()
    {
        return $this->hasMany('App\Models\Package');
    }

    public function tariffs()
    {
        return $this->hasMany('App\Models\Tariff');
    }

    public function active_tariffs()
    {
        return $this->hasMany('App\Models\Tariff')->where('is_active', 1);
    }

    public function address()
    {
        return $this->hasOne('App\Models\Address');
    }

    /**
     * @return HasMany
     */
    public function addresses()
    {
        return $this->hasMany('App\Models\Address')->orderBy('num_order', 'desc');
    }

    /**
     * @return mixed
     */
    public function getCurrencyWithLabelAttribute()
    {
        return config('ase.attributes.currencies')[$this->attributes['currency']];
    }

    public function getFliesPerWeekAttribute()
    {
        $flies = $this->attributes['per_week'];
        $exploded = explode("/", $flies);

        return isset($exploded[1]) ? $exploded[1] : $exploded[0];
    }

    public function getFliesWeekAttribute()
    {
        $flies = $this->attributes['per_week'];
        $exploded = explode("/", $flies);

        return isset($exploded[1]) ? $exploded[0] : null;
    }

    public function calculateDeliveryPrice(
        $weight, //0.2
        $weightUnit = 0, //0
        $width = null, //null
        $height = null, //null
        $length = null, //null
        $sizeUnit = 0, // 0
        $showCurrency = false,
        $discountPercent = 0,
        $azerpoct = 0, //1
        $city_id = 0, //10
        $additional_delivery_price = 0
    )
    {
        $result = 0;
        if (!$this->country)
            return $result;

        $weight = ((float)str_replace(",", ".", $weight)) * config('ase.attributes.weightConvert')[$weightUnit]; //0.2kg

        if ($width) {
            $width = ((float)$width) * config('ase.attributes.lengthConvert')[$sizeUnit];
        }
        if ($height) {
            $height = ((float)$height) * config('ase.attributes.lengthConvert')[$sizeUnit];
        }
        if ($length) {
            $length = ((float)$length) * config('ase.attributes.lengthConvert')[$sizeUnit];
        }

        $size_index = ($this->country->delivery_index && ($width >= 100 || $height >= 100 || $length >= 100)) ? ($width * $height * $length / $this->country->delivery_index) : 0;

        $kq = $size_index > $weight ? $size_index : $weight;


        if ($kq) {
            $tariff_price = null;
            $weightPrice = null;

            $tariffs = $this->active_tariffs;
            if ($tariffs && count($tariffs) > 0) {
                foreach ($tariffs as $tariff) {
                    if (!$tariff->tariff_weights || count($tariff->tariff_weights) <= 0)
                        continue;
                    foreach ($tariff->tariff_weights as $tariffWeight) {
                        $tariffPrices = null;
                        if ($azerpoct)
                            $tariffPrices = $tariffWeight->azerpoct_tariff_prices;
                        else
                            $tariffPrices = $tariffWeight->non_azerpoct_tariff_prices;
                        if (!$tariffPrices || count($tariffPrices) <= 0)
                            continue;
                        //echo "kq=".$kq." from_weight=".$tariffWeight->from_weight." to_weight=".$tariffWeight->to_weight." <br> ";
                        if (
                            ($tariffWeight->from_weight <= $kq || !$tariffWeight->from_weight || $tariffWeight->from_weight <= 0)
                            &&
                            ($tariffWeight->to_weight > $kq || !$tariffWeight->to_weight || $tariffWeight->to_weight <= 0)
                        ) {
                            //echo "  twid=".$tariffWeight->id." pw=".$tariffWeight->per_weight." cid=".$city_id;
                            foreach ($tariffPrices as $tariffPrice) {
                                if ($tariffPrice->city_id && $tariffPrice->city_id == $city_id) {
                                    //echo "tpid=".$tariffPrice->id."  twid=".$tariffWeight->id." pw=".$tariffWeight->per_weight." tp=".$tariffPrice->price." tp=".$tariff_price." cid=".$city_id."  tcid=".$tariffPrice->city_id;
                                    if ($tariffWeight->per_weight && $tariffWeight->per_weight > 0)
                                        $tariff_price = ($kq / $tariffWeight->per_weight) * $tariffPrice->price;
                                    else
                                        $tariff_price = $tariffPrice->price;
                                    break;
                                }
                            }
                            if (!$tariff_price)
                                foreach ($tariffPrices as $tariffPrice) {
                                    //echo "tpid=".$tariffPrice->id."  twid=".$tariffWeight->id." pw=".$tariffWeight->per_weight." tp=".$tariffPrice->price." tp=".$tariff_price." cid=".$city_id."  tcid=".$tariffPrice->city_id;
                                    if (!$tariffPrice->city_id) {
                                        if ($tariffWeight->per_weight && $tariffWeight->per_weight > 0)
                                            $tariff_price = ($kq / $tariffWeight->per_weight) * $tariffPrice->price;
                                        else
                                            $tariff_price = $tariffPrice->price;
                                        break;
                                    }
                                }
                        }
                        if ($tariff_price)
                            break;
                    }
                    if ($tariff_price)
                        break;
                }
            }

            //$weightPrice=WeightPrice::where('warehouse_id',$this->id)->where("is_active",1)
            //	->where(function ($q) use($kq) {$q->where('weight_from','<=',$kq)->orWhereNull('weight_from')->orWhere('weight_from','=',0);})
            //	->where(function ($q) use($kq) {$q->where('weight_to','>',$kq)->orWhereNull('weight_to')->orWhere('weight_to','=',0);})
            //	->orderBy('updated_at', 'desc')->first();

            if ($tariff_price) {
                $result = $tariff_price;
            } else if ($weightPrice) {
                $result = $weightPrice->shipping_amount;
            } else {
                if ($this->per_g) {
                    if ($kq <= 0.5) {
                        $result = $this->half_kg;
                    } else {
                        $result = $this->half_kg + ($this->per_g * ($kq - 0.5) * 1000);
                    }
                } else {
                    $result = $this->per_kg * $kq;
                    if ($kq < 1)
                        $result = $this->per_kg;
                    if ($kq < 1 && $this->from_750g_to_1kq)
                        $result = $this->from_750g_to_1kq;
                    if ($kq < 0.75 && $this->from_500g_to_750g)
                        $result = $this->from_500g_to_750g;
                    if ($kq <= 0.5 && $this->half_kg)
                        $result = $this->half_kg;
                    if ($kq < 0.5 && $this->from_200g_to_500g)
                        $result = $this->from_200g_to_500g;
                    if ($kq < 0.2 && $this->from_100g_to_200g)
                        $result = $this->from_100g_to_200g;
                    if ($kq < 0.1 && $this->to_100g)
                        $result = $this->to_100g;
                    if ($kq >= 10 && $this->up_10_kg)
                        $result = $this->up_10_kg * $kq;
                }
            }
        }
        //echo "d=".$discountPercent." ";
        if ($result && $additional_delivery_price && $additional_delivery_price > 0)
            $result = $result + $additional_delivery_price;
        if ($discountPercent > 0 && $discountPercent < 100)
            $result = $result - round($result * $discountPercent / 100, 2);
        else if ($discountPercent >= 100) $result = 0;
        return $result ? (round($result, 2) . ($showCurrency ? " " . $this->currency_with_label : null)) : False;
    }



    public function calculateDeliveryPrice2(
        $weight, //0.2
        $weightUnit = 0, //0
        $width = null, //null
        $height = null, //null
        $length = null, //null
        $sizeUnit = 0, // 0
        $showCurrency = false,
        $discountPercent = 0,
        $azerpoct = 0, //1
        $city_id = 0, //10
        $additional_delivery_price = 0
    )
    {


        $result = 0;
        if (!$this->country)
            return $result;



        $weight = ((float)str_replace(",", ".", $weight)) * config('ase.attributes.weightConvert')[$weightUnit]; //0.2kg

        if ($width) {
            $width = ((float)$width) * config('ase.attributes.lengthConvert')[$sizeUnit];
        }
        if ($height) {
            $height = ((float)$height) * config('ase.attributes.lengthConvert')[$sizeUnit];
        }
        if ($length) {
            $length = ((float)$length) * config('ase.attributes.lengthConvert')[$sizeUnit];
        }

        $size_index = ($this->country->delivery_index && ($width >= 100 || $height >= 100 || $length >= 100)) ? ($width * $height * $length / $this->country->delivery_index) : 0;

        $kq = $size_index > $weight ? $size_index : $weight;


        if ($kq) {
            $tariff_price = null;
            $weightPrice = null;

            $tariffs = $this->active_tariffs;


            if ($tariffs && count($tariffs) > 0) {
                foreach ($tariffs as $tariff) {
                    if (!$tariff->tariff_weights || count($tariff->tariff_weights) <= 0)
                        continue;

//                    dd($tariff->tariff_weights->toArray());
                    foreach ($tariff->tariff_weights as $tariffWeight) {

                        $tariffPrices = null;
                        if ($azerpoct)
                            $tariffPrices = $tariffWeight->azerpoct_tariff_prices;
                        else
                            $tariffPrices = $tariffWeight->non_azerpoct_tariff_prices;
                        if (!$tariffPrices || count($tariffPrices) <= 0)
                            continue;
                        //echo "kq=".$kq." from_weight=".$tariffWeight->from_weight." to_weight=".$tariffWeight->to_weight." <br> ";
                        if (
                            ($tariffWeight->from_weight <= $kq || !$tariffWeight->from_weight || $tariffWeight->from_weight <= 0)
                            &&
                            ($tariffWeight->to_weight > $kq || !$tariffWeight->to_weight || $tariffWeight->to_weight <= 0)
                        ) {
                            //echo "  twid=".$tariffWeight->id." pw=".$tariffWeight->per_weight." cid=".$city_id;

                            foreach ($tariffPrices as $tariffPrice) {
                                if ($tariffPrice->city_id && $tariffPrice->city_id == $city_id) {
                                    //echo "tpid=".$tariffPrice->id."  twid=".$tariffWeight->id." pw=".$tariffWeight->per_weight." tp=".$tariffPrice->price." tp=".$tariff_price." cid=".$city_id."  tcid=".$tariffPrice->city_id;
                                    if ($tariffWeight->per_weight && $tariffWeight->per_weight > 0)
                                        $tariff_price = ($kq / $tariffWeight->per_weight) * $tariffPrice->price;
                                    else
                                        $tariff_price = $tariffPrice->price;
                                    break;
                                }
                            }

                            if (!$tariff_price)
                                foreach ($tariffPrices as $tariffPrice) {
                                    //echo "tpid=".$tariffPrice->id."  twid=".$tariffWeight->id." pw=".$tariffWeight->per_weight." tp=".$tariffPrice->price." tp=".$tariff_price." cid=".$city_id."  tcid=".$tariffPrice->city_id;
                                    if (!$tariffPrice->city_id) {
                                        if ($tariffWeight->per_weight && $tariffWeight->per_weight > 0)
                                            $tariff_price = ($kq / $tariffWeight->per_weight) * $tariffPrice->price;
                                        else
                                            $tariff_price = $tariffPrice->price;
                                        break;
                                    }
                                }
                        }
                        if ($tariff_price)
                            break;
                    }
                    if ($tariff_price)
                        break;
                }
            }

            dd($city_id);



            //$weightPrice=WeightPrice::where('warehouse_id',$this->id)->where("is_active",1)
            //	->where(function ($q) use($kq) {$q->where('weight_from','<=',$kq)->orWhereNull('weight_from')->orWhere('weight_from','=',0);})
            //	->where(function ($q) use($kq) {$q->where('weight_to','>',$kq)->orWhereNull('weight_to')->orWhere('weight_to','=',0);})
            //	->orderBy('updated_at', 'desc')->first();

//            dd($tariff_price,$weightPrice);



            if ($tariff_price) {
                $result = $tariff_price;
            } else if ($weightPrice) {
                $result = $weightPrice->shipping_amount;
            } else {
                if ($this->per_g) {
                    if ($kq <= 0.5) {
                        $result = $this->half_kg;
                    } else {
                        $result = $this->half_kg + ($this->per_g * ($kq - 0.5) * 1000);
                    }
                } else {
//                    dd($this->id);
//                    dd($this->per_kg,$this->half_kg, $this->from_200g_to_500g, $this->from_100g_to_200g, $this->to_100g, $this->up_10_kg);


                    $result = $this->per_kg * $kq;
                    if ($kq < 1)
                        $result = $this->per_kg;
                    if ($kq < 1 && $this->from_750g_to_1kq)
                        $result = $this->from_750g_to_1kq;
                    if ($kq < 0.75 && $this->from_500g_to_750g)
                        $result = $this->from_500g_to_750g;
                    if ($kq <= 0.5 && $this->half_kg)
                        $result = $this->half_kg;
                    if ($kq < 0.5 && $this->from_200g_to_500g)
                        $result = $this->from_200g_to_500g;
                    if ($kq < 0.2 && $this->from_100g_to_200g)
                        $result = $this->from_100g_to_200g;
                    if ($kq < 0.1 && $this->to_100g)
                        $result = $this->to_100g;
                    if ($kq >= 10 && $this->up_10_kg)
                        $result = $this->up_10_kg * $kq;


                }
            }
        }




        //echo "d=".$discountPercent." ";
        if ($result && $additional_delivery_price && $additional_delivery_price > 0)
            $result = $result + $additional_delivery_price;
        if ($discountPercent > 0 && $discountPercent < 100)
            $result = $result - round($result * $discountPercent / 100, 2);
        else if ($discountPercent >= 100) $result = 0;
        return $result ? (round($result, 2) . ($showCurrency ? " " . $this->currency_with_label : null)) : False;
    }


    public function calculateDeliveryPriceWithManat(
        $weight,
        $weightUnit,
        $width = null,
        $height = null,
        $length = null,
        $sizeUnit
    )
    {
        $price = $this->calculateDeliveryPrice($weight, $weightUnit, $width, $height, $length, $sizeUnit);
        $mult = (getCurrencyRate(1) / getCurrencyRate($this->attributes['currency']));
        $manatPrice = round($price * $mult, 2);

        return $manatPrice;
    }
}
