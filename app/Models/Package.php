<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Carbon\Carbon;
use DB;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;
use App\Models\UkrExpressModel;
use Illuminate\Support\Facades\Artisan;

/**
 * App\Models\Package
 *
 * @property int $id
 * @property int|null $bag_id
 * @property int|null $user_id
 * @property int|null $warehouse_id
 * @property int|null $country_id
 * @property string $custom_id
 * @property mixed $weight
 * @property int $weight_type
 * @property float|null $width
 * @property float|null $height
 * @property float|null $length
 * @property int $length_type
 * @property string|null $tracking_code
 * @property string|null $website_name
 * @property int|null $type_id
 * @property int|null $number_items
 * @property float|null $shipping_amount
 * @property int $shipping_amount_cur
 * @property string $invoice
 * @property string|null $user_comment
 * @property string|null $warehouse_comment
 * @property string|null $screen_file
 * @property string|null $admin_comment
 * @property int $show_label
 * @property int|null $declaration
 * @property float|null $delivery_price
 * @property int $status
 * @property int $paid
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $processed_at
 * @property-read Country|null $country
 * @property-read mixed $country_flag
 * @property-read mixed $delivery_price_with_label
 * @property-read string $full_size
 * @property-read mixed $paid_with_label
 * @property-read string $shipping_price
 * @property-read mixed $show_label_with_label
 * @property-read mixed $size
 * @property-read mixed $size_unit
 * @property-read mixed $status_label
 * @property-read mixed $status_with_label
 * @property-read mixed $total_price
 * @property-read mixed $web_site_logo
 * @property-read mixed $weight_unit
 * @property-read string $weight_with_type
 * @property-read Collection|PackageLog[] $logs
 * @property-read Collection|PackageOwner[] $owners
 * @property-read PackageType|null $type
 * @property-read User|null $user
 * @property-read PackageCarrier|null $carrier
 * @property-read ActivityWorker|null $activityworker
 * @property-read Warehouse|null $warehouse
 * @method static Builder|Package done()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Package onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|Package whereAdminComment($value)
 * @method static Builder|Package whereCountryId($value)
 * @method static Builder|Package whereCreatedAt($value)
 * @method static Builder|Package whereCustomId($value)
 * @method static Builder|Package whereDeclaration($value)
 * @method static Builder|Package whereDeletedAt($value)
 * @method static Builder|Package whereDeliveryPrice($value)
 * @method static Builder|Package whereHeight($value)
 * @method static Builder|Package whereId($value)
 * @method static Builder|Package whereInvoice($value)
 * @method static Builder|Package whereLength($value)
 * @method static Builder|Package whereLengthType($value)
 * @method static Builder|Package whereNumberItems($value)
 * @method static Builder|Package wherePaid($value)
 * @method static Builder|Package whereScreenFile($value)
 * @method static Builder|Package whereShippingAmount($value)
 * @method static Builder|Package whereShippingAmountCur($value)
 * @method static Builder|Package whereShowLabel($value)
 * @method static Builder|Package whereStatus($value)
 * @method static Builder|Package whereTrackingCode($value)
 * @method static Builder|Package whereTypeId($value)
 * @method static Builder|Package whereUpdatedAt($value)
 * @method static Builder|Package whereUserComment($value)
 * @method static Builder|Package whereUserId($value)
 * @method static Builder|Package whereWarehouseComment($value)
 * @method static Builder|Package whereWarehouseId($value)
 * @method static Builder|Package whereWebsiteName($value)
 * @method static Builder|Package whereWeight($value)
 * @method static Builder|Package whereWeightType($value)
 * @method static Builder|Package whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|Package withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Package withoutTrashed()
 * @mixin Eloquent
 * @property-read mixed $total_price_with_label
 * @property-read mixed $delivery_manat_price
 * @property-read int|null $logs_count
 * @property-read int|null $owners_count
 * @method static Builder|Package newModelQuery()
 * @method static Builder|Package newQuery()
 * @method static Builder|Package query()
 * @property string|null $other_type
 * @property int $dec_message
 * @property-read mixed $merged_delivery_price
 * @property-read Collection|Parcel[] $parcel
 * @property-read Collection|Bag[] $bag
 * @property-read int|null $parcel_count
 * @property-read int|null $bag_count
 * @property-read Transaction $portmanat
 * @method static Builder|Package likeTracking($code)
 * @method static Builder|Package whereDecMessage($value)
 * @method static Builder|Package whereOtherType($value)
 * @property float|null $gross_weight
 * @method static Builder|Package ready()
 * @method static Builder|Package whereGrossWeight($value)
 */
class Package extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    /**
     * @var string
     */
    public $uploadDir = 'uploads/packages/';

    /**
     * @var array
     */
    protected $appends = ['full_size', 'weight_with_type', 'status_label', 'shipping_price', 'total_price'];

    /**
     * @var array
     */
    ///public $with = ['carrier','type', 'warehouse', 'user.dealer', 'country', 'manager','activityworker'];
    public $with = ['carrier', 'type', 'warehouse', 'user.dealer', 'country', 'activityworker'];

    /**
     * @var array
     */
    public $dates = ['deleted_at', 'scanned_at', 'sent_at', 'requested_at', 'processed_at'];

    const STATES = [
        'InWarehouse' => 0,
        'Sent' => 1,
        'InBaku' => 2,
        'InKobia' => 8,
        'Done' => 3,
        'InCustoms' => 4,
        'Rejected' => 5,
        'EarlyDeclaration' => 6,
    ];

    protected $fillable = [
        'bot_comment',
        'status',
        'debt_price'
    ];

    /* * * * * * * *
     *  Relations *
     * * * * * * * */
    /**
     * @return BelongsTo
     */

    private $cm;
    public $do_use_goods = false;
    private $costsUSD = null;
    private $shippingPrice = null;

    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function getCityNameAttribute()
    {
        if (!$this->city_id)
            return NULL;
        $city = $this->city();
        if ($city && $city->first())
            return $city->first()->name;
        return NULL;
    }

    public function getAzeriexpressstatusLabelAttribute()
    {
        if (!$this->azeriexpresspackage) {
            return '-';
        }

        return __('admin.azeriexpress_warehouse_package_status_' . $this->azeriexpresspackage->status);
    }

    public function getYenipoctstatusLabelAttribute()
    {
        if (!$this->yenipoctpackage) {
            return '-';
        }

        return __('admin.yenipoct_warehouse_package_status_' . $this->yenipoctpackage->status);
    }

    public function getKargomatstatusLabelAttribute()
    {
        if (!$this->kargomatpackage) {
            return '-';
        }

        return __('admin.kargomat_warehouse_package_status_' . $this->kargomatpackage->status);
    }

    public function getSuratstatusLabelAttribute()
    {
        if (!$this->suratpackage) {
            return '-';
        }

        return __('admin.surat_warehouse_package_status_' . $this->suratpackage->status);
    }

    public function getPrecinctstatusLabelAttribute()
    {
        if (!$this->precinctpackage || $this->azeriexpresspackage || $this->azerpostpackage) {
            return '-';
        }

        return __('admin.precinct_warehouse_package_status_' . $this->precinctpackage->status);
    }

    public function getAzerpostsTesttatusLabelAttribute()
    {
        if (!$this->azerpostpackage) {
            return '-';
        }
        return $this->azerpostpackage->status;
    }
    public function getAzerpoststatusLabelAttribute()
    {
        if (!$this->azerpostpackage) {
            return '-';
        }
        return __('admin.azerpost_warehouse_package_status_' . $this->azerpostpackage->status);
    }


    public function unknown_office()
    {
        return $this->belongsTo('App\Models\UnknownOffice', 'unknown_office_id');
    }

    public function is_unknown_office()
    {
        if ($this->store_status && $this->delivery_point) return false;
        if ($this->azeriexpress_office_id && $this->azeriexpress_office) return false;
        if ($this->azerpost_office_id && $this->azerpost_office) return false;
        if ($this->surat_office_id && $this->surat_office) return false;
        if ($this->yenipoct_office_id && $this->yenipoct_office) return false;
        if ($this->kargomat_office_id && $this->kargomat_office) return false;
        if ($this->unknown_office_id && $this->unknown_office) return true;
        return false;
    }

    public function getFilialNameAttribute()
    {
        if ($this->store_status && $this->delivery_point) return $this->delivery_point->description . ' (ASE)';
        if ($this->azeri_express_office_id && $this->azeri_express_office) return $this->azeri_express_office->description . ' (AZXP)';
        if ($this->azerpost_office_id && $this->azerpost_office) return $this->azerpost_office->name . ' (AZPOST)';
        if ($this->surat_office_id && $this->surat_office) return $this->surat_office->description . ' (SURAT)';
        if ($this->yenipoct_office_id && $this->yenipoct_office) return $this->yenipoct_office->description . ' (YeniPoct)';
        if ($this->kargomat_office_id && $this->kargomat_office) return $this->kargomat_office->description . ' (Kargomat)';
        if ($this->unknown_office_id && $this->unknown_office) return $this->unknown_office->description . ' (UNKNOWN)';
        return '';
    }

    public function getFilialDetailsAttribute()
    {
        if ($this->store_status && $this->delivery_point) return $this->delivery_point . ' _(ASE)';
        if ($this->azeri_express_office_id && $this->azeri_express_office) return $this->azeri_express_office . ' _(AZEXP)';
        if ($this->azerpost_office_id && $this->azerpost_office) return $this->azerpost_office . ' _(AZPOST)';
        if ($this->surat_office_id && $this->surat_office) return $this->surat_office . ' _(SURAT)';
        if ($this->yenipoct_office_id && $this->yenipoct_office) return $this->yenipoct_office . ' _(YP)';
        if ($this->kargomat_office_id && $this->kargomat_office) return $this->kargomat_office . ' _(Kargomat)';
        if ($this->unknown_office_id && $this->unknown_office) return $this->unknown_office . ' _(UNKNOWN)';
        return null;
    }

    public function azeriexpress_office()
    {
        return $this->belongsTo(AzeriExpress\AzeriExpressOffice::class, 'azeri_express_office_id');
    }

    public function azerpost_office()
    {
        return $this->belongsTo('App\Models\Azerpost\AzerpostOffice', 'azerpost_office_id');
    }

    public function surat_office()
    {
        return $this->belongsTo('App\Models\Surat\SuratOffice', 'surat_office_id');
    }

    public function yenipoct_office()
    {
        return $this->belongsTo('App\Models\YeniPoct\YenipoctOffice', 'yenipoct_office_id');
    }

    public function kargomat_office()
    {
        return $this->belongsTo('App\Models\Kargomat\KargomatOffice', 'kargomat_office_id');
    }

    public function delivery_point()
    {
        return $this->belongsTo(DeliveryPoint::class, 'store_status');
    }

    public function azeri_express_office()
    {
        return $this->belongsTo(AzeriExpress\AzeriExpressOffice::class, 'azeri_express_office_id');
    }

    public function azeriexpresspackage()
    {
        return $this->belongsTo('App\Models\AzeriExpress\AzeriExpressPackage', 'id', 'package_id')->where('type', 'package');
    }

    public function precinctpackage()
    {
        return $this->belongsTo('App\Models\Precinct\PrecinctPackage', 'id', 'package_id')->where('type', 'package');
    }

    public function azerpostpackage()
    {
        return $this->belongsTo('App\Models\Azerpost\AzerpostPackage', 'id', 'package_id')->where('type', 'package');
    }

    public function suratpackage()
    {
        return $this->belongsTo('App\Models\Surat\SuratPackage', 'id', 'package_id')->where('type', 'package');
    }

    public function yenipoctpackage()
    {
        return $this->belongsTo('App\Models\YeniPoct\YenipoctPackage', 'id', 'package_id')->where('type', 'package');
    }

    public function kargomatpackage()
    {
        return $this->belongsTo('App\Models\Kargomat\KargomatPackage', 'id', 'package_id')->where('type', 'package');
    }

    public function parcel()
    {
        return $this->belongsToMany(Parcel::class, 'parcel_package', 'package_id');
    }

    public function bag()
    {
        return $this->belongsToMany(Bag::class, 'bag_package', 'package_id');
        //return $this->belongsTo('App\Models\Bag');
    }

    public function main_parcel()
    {
        return ($this->belongsToMany(Parcel::class, 'parcel_package'))->first();
    }

    /**
     * @return BelongsTo
     */
    public function carrier()
    {
        //return $this->belongsTo('App\Models\PackageCarrier', 'id','package_id')->withTrashed();
        return $this->belongsTo('App\Models\PackageCarrier', 'id', 'package_id');
    }

    public function azerpoct()
    {
        return $this->belongsTo('App\Models\PackageAzerPoct', 'id', 'package_id');
    }

    /**
     * @return HasMany
     */
    public function goods()
    {
        return $this->hasMany('App\Models\PackageGood', 'package_id');
    }

    function updateCarrier()
    {
        if ($this->cm == null) {
            $this->cm = new CustomsModel();
            $this->cm->retryCount = 2;
            $this->cm->retrySleep = 0;
        }
        $isCommercial = 0;
        $carrier = PackageCarrier::where('package_id', $this->id)->first();
        if ($carrier)
            $isCommercial = $carrier->is_commercial;
        else {
            $user = User::where('id', $this->user_id)->first();
            if ($user)
                $isCommercial = $user->is_commercial;
        }
        $this->cm->isCommercial = $isCommercial;
        $this->cm->trackingNumber = $this->custom_id;
        $cpost = $this->cm->get_carrierposts2();
        if ($cpost->code == 200 && !empty($cpost->inserT_DATE)) {
            $ldate = date('Y-m-d H:i:s');
            $this->cm->updateDB($this->id, $this->fin, $this->custom_id, $ldate, $cpost);
        }
        $this->carrier = PackageCarrier::where('package_id', $this->id)->first();

    }

    public function activityworker()
    {
        return $this->belongsTo('App\Models\ActivityWorker', 'id', 'content_id');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function shelf()
    {
        return $this->belongsTo('App\Models\CourierShelf', 'shelf_id');
    }

    public function courier_delivery()
    {
        return $this->belongsTo('App\Models\CD')->withTrashed();
    }

    /**
     * @return BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }

    public function getCountryFlagAttribute()
    {
        $country = $this->defaultCountry();

        return $country ? $country->flag : null;
    }

    /**
     * @return BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\Models\PackageType', 'type_id')->withTrashed();
    }

    /**
     * @return HasMany
     */
    public function owners()
    {
        return $this->hasMany('App\Models\PackageOwner');
    }

    /**
     * @return HasMany
     */
    public function logs()
    {
        return $this->hasMany(PackageLog::class);
    }

    public function portmanat()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->whereIn('paid_for', [
            'PACKAGE_BALANCE',
            'PACKAGE',
        ])->where('paid_by', 'PORTMANAT');
    }

    public function kapital()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->whereIn('paid_for', [
            'PACKAGE_BALANCE',
            'PACKAGE',
        ])->where('paid_by', 'KAPITAL');
    }


    /* * * * * * * *
     * Attributes *
     * * * * * * */

    /**
     * @param $value
     * @return mixed
     */
    public function getWeightAttribute($value)
    {
        if (!empty($value))
            return $value + 0;
    }

    public function getWeightGoodsAttribute($value)
    {
        if (!empty($value))
            return $value + 0;
    }

    public function getZipCodeAttribute()
    {
        if (!isset($this->user) || !$this->user) return '';
        return $this->user->zip_code;
    }

    public function getAzerpoctStatusAttribute()
    {
        if ($this->azerpoct_send && $this->azerpoct) {
            return trans('front.azerpoct_status_' . $this->azerpoct->status, ['zip_code' => $this->zip_code]) . '<br>Order ID: ' . $this->azerpoct->order_id;
        }
        return '';
    }

    public function getWeightUnitAttribute()
    {
        return config('ase.attributes.weight')[$this->attributes['weight_type']];
    }

    /**
     * @return string
     */
    public function getWeightWithTypeAttribute()
    {
        $weight = $this->attributes['weight_goods'];
        if (empty($weight))
            $weight = $this->attributes['weight'];
        return ($weight + 0) . ' ' . config('ase.attributes.weight')[$this->attributes['weight_type']];
    }

    /**
     * @return string
     */
    public function getFullSizeAttribute()
    {
        if (!$this->attributes['length'] && !$this->attributes['width'] && !$this->attributes['height']) return '';
        return ($this->attributes['length'] + 0) . '/' . ($this->attributes['width'] + 0) . '/' . ($this->attributes['height'] + 0) . " " . config('ase.attributes.length')[$this->attributes['length_type']];
    }

    /**
     * @return string
     */
    public function getTrackingAttribute()
    {
        return $this->custom_id;
    }

    public function getSizeAttribute()
    {
        if (!$this->attributes['length'] && !$this->attributes['width'] && !$this->attributes['height']) return '';

        return ($this->attributes['length'] + 0) . '/' . ($this->attributes['width'] + 0) . '/' . ($this->attributes['height'] + 0);
    }

    public function getSizeUnitAttribute()
    {
        return config('ase.attributes.length')[$this->attributes['length_type']];
    }

    /**
     * @return mixed
     */
    public function getStatusLabelAttribute()
    {
        return config('ase.attributes.package.status')[$this->attributes['status']];
    }

    public function getShippingConvertedPriceAttribute()
    {
        $country = $this->defaultCountry();

        $shipping_amount = $this->shipping_amount_goods;
        if (empty($shipping_amount))
            $shipping_amount = $this->shipping_amount;
        return $country && $country->convert_invoice_to_usd ? $this->getShippingAmountUSD() : $shipping_amount;
    }

    public function getAdditionalDeliveryPricesAttribute()
    {
        $str = '';
        if ($this->additional_delivery_final_price && $this->additional_delivery_final_price > 0) {
            if (!empty($str)) $str .= ' + ';
            $str .= 'Additional: ' . $this->additional_delivery_final_price;
        }
        if ($this->battery_price && $this->battery_price > 0) {
            if (!empty($str)) $str .= ' + ';
            $str .= 'Battery: ' . $this->battery_price;
        }
        if ($this->insurance_price && $this->insurance_price > 0) {
            if (!empty($str)) $str .= ' + ';
            $str .= 'Insurance: ' . $this->insurance_price;
        }
        return $str;
    }

    public function getShippingAmountUsd1Attribute()
    {
        return $this->getShippingAmountUSD();
    }

    public static function s_getShippingAmountUSD($query)
    {
        $shippingAmountUSD = $query->shipping_amount_usd;
        if (!empty($shippingAmountUSD))
            return $shippingAmountUSD;

        $shippingAmount = $query->shipping_amount_goods;
        if (empty($shippingAmount))
            $shippingAmount = $query->shipping_amount;
        if (empty($shippingAmount))
            return $shippingAmount;
        $shippingAmountUSD = number_format(0 + round($shippingAmount * 1 / getCurrencyRate($query->attributes['shipping_amount_cur']), 2), 2, ".", "");

        $query->shipping_amount_usd = $shippingAmountUSD;
        return $shippingAmountUSD;
    }

    public function getShippingAmountUSD()
    {
        $shippingAmountUSD = $this->shipping_amount_usd;
        if (!empty($shippingAmountUSD))
            return $shippingAmountUSD;

        $shippingAmount = $this->shipping_amount_goods;
        if (empty($shippingAmount))
            $shippingAmount = $this->shipping_amount;
        if (empty($shippingAmount))
            return $shippingAmount;
        $shippingAmountUSD = number_format(0 + round($shippingAmount * 1 / getCurrencyRate($this->attributes['shipping_amount_cur']), 2), 2, ".", "");

        $ldate = date('Y-m-d H:i:s');
        if ($this->id) {
            $str = "update packages set shipping_amount_usd=?,shipping_amount_usd_at=? where id=?";
            DB::update($str, [$shippingAmountUSD, $ldate, $this->id]);
            $this->shipping_amount_usd = $shippingAmountUSD;
        }

        return $shippingAmountUSD;
    }

    public function getShippingAmountRUB()
    {
        $shippingAmountRUB = $this->shipping_amount_rub;
        if (!empty($shippingAmountRUB))
            return $shippingAmountRUB;

        $shippingAmount = $this->shipping_amount_goods;
        if (empty($shippingAmount))
            $shippingAmount = $this->shipping_amount;
        if (empty($shippingAmount))
            return $shippingAmount;
        if ($this->attributes['shipping_amount_cur'] == 4)
            $shippingAmountRUB = number_format(0 + round($shippingAmount, 2), 2, ".", "");
        else
            $shippingAmountRUB = number_format(0 + round($shippingAmount * getCurrencyRate(4) / getCurrencyRate($this->attributes['shipping_amount_cur']), 2), 2, ".", "");

        $ldate = date('Y-m-d H:i:s');
        $str = "update packages set shipping_amount_rub=?,shipping_amount_rub_at=? where id=?";
        DB::update($str, [$shippingAmountRUB, $ldate, $this->id]);
        $this->shipping_amount_rub = $shippingAmountRUB;

        return $shippingAmountRUB;
    }

    public function getDeliveryPriceAZNWithDiscount($weight = 0)
    {
        return $this->getSumWithDiscounts($this->getDeliveryPriceAZN($weight), true, false);
    }

    public function getDeliveryPriceUSDWithDiscount($weight = 0)
    {
        return $this->getSumWithDiscounts($this->getDeliveryPriceUSD($weight), false, true);
    }

    public function getDeliveryPriceWithDiscount($weight = 0)
    {
        return $this->getSumWithDiscounts($this->getDeliveryPrice(0, $weight));
    }

    public function getSumFromAZN($sumAZN)
    {
        $mult = (getCurrencyRate($this->warehouse->currency) / getCurrencyRate(1));
        return round($sumAZN * $mult, 2);
    }

    public function getSumFromAZNToUSD($sumAZN)
    {
        $mult = (getCurrencyRate(0) / getCurrencyRate(1));
        return round($sumAZN * $mult, 2);
    }

    public function getDeliveryPriceAZN($weight = 0)
    {
        if ($weight == 0) {
            $this->updateDeliveryPrices();
            return $this->delivery_price_azn;
        }
        $delivery_price = $this->getDeliveryPrice(0, $weight);
        if (!$delivery_price) {
            return 0;
        }
        $mult = (getCurrencyRate(1) / getCurrencyRate($this->warehouse->currency));
        return round($delivery_price * $mult, 2);
    }

    public function getDeliveryPriceUSD($weight = 0)
    {
        if ($weight == 0) {
            $this->updateDeliveryPrices();
            return $this->delivery_price_usd;
        }
        $delivery_price = $this->getDeliveryPrice(0, $weight);
        if (!$delivery_price) {
            return 0;
        }
        $mult = (getCurrencyRate(0) / getCurrencyRate($this->warehouse->currency));
        return round($delivery_price * $mult, 2);
    }

    public function getDeliveryPrice($value, $weight = 0)
    {
//        dd($value, $weight);
        if (!$this->warehouse) {
            return null;
        }
        $user = User::find($this->attributes['user_id']);
        $azerpoct = 0;
        $city_id = 0;
        if ($user) {
            $azerpoct = $user->azerpoct_send;
            $city_id = $user->city_id;
        }

        $dweight = $weight;
        if ($dweight <= 0)
            $dweight = $this->getWeight();

        if (!$dweight) return $value;

        $additionalDeliveryPrice = $this->additional_delivery_final_price + $this->battery_price + $this->insurance_price;

        $params = [
            'additional_delivery_final_price' => $this->additional_delivery_final_price,
            'battery_price' => $this->battery_price,
            'insurance_price' => $this->insurance_price,
        ];


//        return $params;

        return $value ? $value : $this->warehouse->calculateDeliveryPrice2($dweight, $this->attributes['weight_type'], $this->attributes['width'], $this->attributes['height'], $this->attributes['length'], $this->attributes['length_type'], false, 0, $azerpoct, $city_id, $additionalDeliveryPrice, $this->custom_id);
    }

    public function getParcelIdAttribute()
    {
        return $this->parcel && $this->parcel->count() ? $this->parcel->first()->id : NULL;
    }

    function updateDeliveryPrices()
    {
        $deliveryPriceUSD = $this->delivery_price_usd;
        $deliveryPriceAZN = $this->delivery_price_azn;

        if (!empty($deliveryPriceUSD) && !empty($deliveryPriceAZN))
            return;
        if (!$this->warehouse || !$this->delivery_price) {
            return;
        }


        $usdCur = 0;
        $warCur = $this->warehouse->currency;
        if ($usdCur == $warCur)
            $deliveryPriceUSD = $this->delivery_price;
        else {
            $mult = (getCurrencyRate($usdCur) / getCurrencyRate($warCur));
            $deliveryPriceUSD = round($this->delivery_price * $mult, 2);
        }

        $mult = (getCurrencyRate(1) / getCurrencyRate($this->warehouse->currency));
        $deliveryPriceAZN = round($this->delivery_price * $mult, 2);

        $ldate = date('Y-m-d H:i:s');
        $str = "update packages set delivery_price_azn=?,delivery_price_azn_at=?,delivery_price_usd=?,delivery_price_usd_at=? where id=?";
        DB::update($str, [$deliveryPriceAZN, $ldate, $deliveryPriceUSD, $ldate, $this->id]);
        $this->delivery_price_azn = $deliveryPriceAZN;
        $this->delivery_price_usd = $deliveryPriceUSD;
    }


    function updateDeliveryPricesTest()
    {
        $deliveryPriceUSD = $this->delivery_price_usd;
        $deliveryPriceAZN = $this->delivery_price_azn;
//        if (!empty($deliveryPriceUSD) && !empty($deliveryPriceAZN))
//            return;
//        if (!$this->warehouse || !$this->delivery_price) {
//            return;
//        }

        $usdCur = 0;
        $warCur = $this->warehouse->currency;
        if ($usdCur == $warCur)
            $deliveryPriceUSD = $this->delivery_price;
        else {
            $mult = (getCurrencyRate($usdCur) / getCurrencyRate($warCur));
            $deliveryPriceUSD = round($this->delivery_price * $mult, 2);
        }

        $mult = (getCurrencyRate(1) / getCurrencyRate($this->warehouse->currency));
        $deliveryPriceAZN = round($this->delivery_price * $mult, 2);

        $ldate = date('Y-m-d H:i:s');

        dd([$deliveryPriceAZN, $deliveryPriceUSD, $this->id, $mult, $this->delivery_price]);

//        $str = "update packages set delivery_price_azn=?,delivery_price_azn_at=?,delivery_price_usd=?,delivery_price_usd_at=? where id=?";
//        DB::update($str, [$deliveryPriceAZN, $ldate, $deliveryPriceUSD, $ldate, $this->id]);
        $this->delivery_price_azn = $deliveryPriceAZN;
        $this->delivery_price_usd = $deliveryPriceUSD;
    }

    public function getShippingPriceRuAttribute()
    {
        $country = $this->defaultCountry();

        $shipping_amount = $this->shipping_amount_goods;
        if (empty($shipping_amount))
            $shipping_amount = $this->shipping_amount;

        return $shipping_amount ? ($this->getShippingAmountRUB() . " RUB") : "-";
    }


    public function getCarriersCostWithLabelAttribute()
    {
        $cost = 0;
        $costUSD = 0;
        $currency = 0;
        if ($this->carrier) {
            $cost = $this->carrier->cost;
            $costUSD = $this->carrier->cost_usd;
            $currency = $this->carrier->currency;
        }
        if (!$cost) return '-';
        $arr = config('ase.attributes.customsCurrencies');
        if (array_key_exists($currency, $arr)) {
            return $cost . ' ' . $arr[$currency];
        } else {
            return $cost . ' Unknown(' . $currency . ')';
        }
    }

    public function getShippingPriceCustomsAttribute()
    {
        if ($this->shipingPrice)
            return $this->shippingPrice;
        $customs_currencies = array(840 => 'USD', 932 => 'AZN', 978 => 'EUR', 949 => 'TRY', 643 => 'RUB', 826 => 'GBP', 156 => 'CNY', 784 => 'AED');
        $cost = 0;
        $costUSD = 0;
        $currency = 0;
        if ($this->carrier) {
            $cost = $this->carrier->cost;
            $costUSD = $this->carrier->cost_usd;
            $currency = $this->carrier->currency;
        }
        /*if($cost<=0) {
            $cm=new CustomsModel();
            $cm->isCommercial=$this->user->is_commercial;
            $cm->trackingNumber=$this->custom_id;
            $res=$cm->getCost();
            if(!$res || $res->error || !$res->cost || $res->cost<=0) {
               $this->shippingPrice=$this->getShippingPriceAttribute();
               return  $this->shippingPrice;
            }
            $cost=$res->cost;
            $costUSD=$res->costUSD;
            $currency=$res->currency;
        }*/
        if ($cost <= 0) {
            $this->shippingPrice = $this->getShippingPriceAttribute();
            return $this->shippingPrice;
        }
        $this->costUSD = $costUSD;
        if (!array_key_exists($currency, $customs_currencies)) {
            $this->shippingPrice = $this->getShippingPriceAttribute();
            return $this->shippingPrice;
        }
        $this->shippingPrice = $cost . " " . $customs_currencies[$currency];
        return $this->shippingPrice;
    }

    public function getShippingPriceAttribute()
    {
        $country = $this->defaultCountry();

        $shipping_amount = $this->shipping_amount_goods;
        if (empty($shipping_amount))
            $shipping_amount = $this->shipping_amount;

        return $shipping_amount ? ($country && $country->convert_invoice_to_usd ? ($this->getShippingAmountUSD() . " USD") : ($shipping_amount . " " . config('ase.attributes.currencies')[$this->attributes['shipping_amount_cur']])) : "-";
    }

    public function getShippingOrgPriceAttribute()
    {
        $shipping_amount = $this->shipping_amount_goods;
        if (empty($shipping_amount))
            $shipping_amount = $this->shipping_amount;
        return $shipping_amount ? ($shipping_amount . " " . config('ase.attributes.currencies')[$this->attributes['shipping_amount_cur']]) : '-';
    }

    /**
     * @param $value
     * @return string
     */
    public function getInvoiceAttribute($value)
    {
        $invoice = ($this->warehouse && ($this->warehouse->allow_make_fake_invoice || $this->warehouse->no_invoice)) ? route('custom_invoice', $this->id) : null;

        $url = asset('no_image.jpg');
        if (file_exists(public_path() . '/' . $this->uploadDir . $value)) {
            $url = asset($this->uploadDir . $value);
        }

        return $value ? $url : $invoice;
    }

    public function getFakeInvoiceAttribute()
    {
        if (!$this->warehouse || $this->warehouse->id != 2)
            return $this->invoice;
        $invoice = ($this->warehouse && ($this->warehouse->allow_make_fake_invoice || $this->warehouse->no_invoice)) ? route('custom_invoice', $this->id) : null;

        return $invoice ? $invoice : $this->invoice;
    }

    /**
     * @return mixed
     */
    public function getStatusWithLabelAttribute()
    {
        return config('ase.attributes.package.status')[$this->attributes['status']];
    }

    /**
     * @return mixed
     */
    public function getPaidWithLabelAttribute()
    {
        return config('ase.attributes.package.paid')[$this->attributes['paid']];
    }

    public function getPaidAttWithLabelAttribute()
    {
        $list = [
            0=>'No',
            1=>'Yes',
        ];

        $item = $list[(int)$this->attributes['paid']];

        return $item ?? '-';
    }
    public function getPaidDebtAttWithLabelAttribute()
    {
        $json = config('ase.attributes.package.paidWithLabelDebt');

        $fixedJson = preg_replace('/(\w+):/', '"$1":', $json);
        $list = json_decode($fixedJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Error json';
        }

        $item = collect($list)->firstWhere('value', (int)$this->attributes['paid_debt']);

        return $item['text'] ?? '-';
    }

    public function getStopDebtAttWithLabelAttribute()
    {
        $json = config('ase.attributes.package.stopDebt');

        $fixedJson = preg_replace('/(\w+):/', '"$1":', $json);
        $list = json_decode($fixedJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return 'Error json';
        }

        $item = collect($list)->firstWhere('value', (int)$this->attributes['stop_debt']);

        return $item['text'] ?? '-';
    }

    public function getShowLabelWithLabelAttribute()
    {
        return config('ase.attributes.package.label')[$this->attributes['show_label']];
    }

    public function getWeight()
    {
        $weight = null;
        if (array_key_exists('weight_goods', $this->attributes))
            $weight = $this->attributes['weight_goods'];
        if (empty($weight) && array_key_exists('weight', $this->attributes))
            $weight = $this->attributes['weight'];
        return $weight;
    }

    public function getNumberItems()
    {
        $number_items = null;
        if (array_key_exists('number_items_goods', $this->attributes))
            $number_items = $this->attributes['number_items_goods'];
        if (empty($number_items) && array_key_exists('number_items', $this->attributes))
            $number_items = $this->attributes['number_items'];
        return $number_items;
    }

    public function getShippingAmount()
    {
        $shipping_amount = null;
        if (array_key_exists('shipping_amount_goods', $this->attributes))
            $shipping_amount = $this->attributes['shipping_amount_goods'];
        if (empty($shipping_amount) && array_key_exists('shipping_amount', $this->attributes))
            $shipping_amount = $this->attributes['shipping_amount'];
        return $shipping_amount;
    }

    public function getDeliveryPriceAttribute($value)
    {
        return $this->getDeliveryPrice($value);
    }

    public function getDeliveryManatPriceAttribute($value)
    {
        return $this->getDeliveryPriceAZN();
    }

    public function getDeliveryUSDPriceAttribute($value)
    {
        return $this->getDeliveryPriceUSD();
    }

    public function getDeliveryPriceDiscountAttribute()
    {
        return $this->getSumWithDiscounts($this->delivery_price);
    }

    public function getSumWithDiscounts($sum, $azn = false, $usd = false)
    {
        $totalSum = $sum;
        if (empty($totalSum) || $totalSum <= 0)
            return $totalSum;
        $promoDiscountPercent = $this->promo_discount_percent;

        $promoDiscountAmount = $this->promo_discount_amount;
        if ($azn)
            $promoDiscountAmount = $this->promo_discount_amount_azn;
        else if ($usd)
            $promoDiscountAmount = $this->promo_discount_amount_usd;

        $promoDiscountWeightAmount = $this->promo_discount_weight_amount;
        if ($azn)
            $promoDiscountWeightAmount = $this->promo_discount_weight_amount_azn;
        else if ($usd)
            $promoDiscountWeightAmount = $this->promo_discount_weight_amount_usd;

        $promoDiscountWeight = $this->promo_discount_weight;
        $discountPercent = $this->discount_percent;
        $ulduzumDiscountPercent = $this->ulduzum_discount_percent;

        if ($totalSum > 0 && !empty($discountPercent) && $discountPercent > 0)
            $totalSum = $totalSum - ($totalSum * $discountPercent) / 100;
        if ($totalSum > 0 && !empty($promoDiscountWeight) && ($promoDiscountWeight > 0) && ($promoDiscountWeight >= $this->getWeight()))
            $totalSum = 0;
        if ($totalSum > 0 && !empty($promoDiscountWeightAmount) && $promoDiscountWeightAmount > 0)
            $totalSum = $totalSum - $promoDiscountWeightAmount;
        if ($totalSum > 0 && !empty($promoDiscountAmount) && $promoDiscountAmount > 0)
            $totalSum = $totalSum - $promoDiscountAmount;
        if ($totalSum > 0 && !empty($promoDiscountPercent) && $promoDiscountPercent > 0)
            $totalSum = $totalSum - ($totalSum * $promoDiscountPercent) / 100;
        if ($totalSum > 0 && !empty($ulduzumDiscountPercent) && $ulduzumDiscountPercent > 0)
            $totalSum = $totalSum - ($totalSum * $ulduzumDiscountPercent) / 100;

        if ($totalSum < 0) $totalSum = 0;
        return round($totalSum, 2);
    }

    public function getDeliveryUSDPriceDiscountAttribute()
    {
        return $this->getSumWithDiscounts($this->delivery_usd_price, false, true);
    }

    public function getDeliveryManatPriceDiscountAttribute()
    {
        return $this->getSumWithDiscounts($this->delivery_manat_price, true, false);
    }

    public function getMergedDeliveryPriceAttribute()
    {
        return $this->delivery_price ? ("$" . $this->getDeliveryPriceUSD() . "/" . $this->getDeliveryPriceAZN() . "₼") : '-';
    }

    public function getMergedDeliveryPriceDiscountAttribute()
    {
        return $this->delivery_price ? ("$" . $this->delivery_usd_price_discount . "/" . $this->delivery_manat_price_discount . "₼") : '-';
    }

    public function getDeliveryManatPriceWithLabelAttribute()
    {
        if (!$this->warehouse) {
            return '-';
        }

        return $this->delivery_manat_price ? ($this->delivery_manat_price . " AZN") : '-';
    }

    public function getDeliveryManatPriceDiscountWithLabelAttribute()
    {
        if (!$this->warehouse) {
            return '-';
        }

        return $this->delivery_manat_price_discount ? ($this->delivery_manat_price_discount . " AZN") : '-';
    }

    public function getDeliveryPriceWithLabelAttribute()
    {
        if (!$this->warehouse) {
            return '-';
        }

        return $delivery_price . " " . $this->warehouse->currency_with_label;

        return ($this->delivery_price_discount ? ($this->delivery_price_discount . " AZN") : '-') . $this->warehouse->currency_with_label;

        $delivery_price = $this->getDeliveryPrice();
        if (empty($delivery_price))
            return '-';
        if (!$this->warehouse) {
            return '-';
        }
        return $this->delivery_price . " " . $this->warehouse->currency_with_label;
        $user = User::find($this->attributes['user_id']);
        $azerpoct = 0;
        $city_id = 0;
        if ($user) {
            $azerpoct = $user->azerpoct_send;
            $city_id = $user->city_id;
        }
        $weight = $this->attributes['weight_goods'];
        if (empty($weight))
            $weight = $this->attributes['weight'];

        $additionalDeliveryPrice = $this->additional_delivery_final_price + $this->battery_price + $this->insurance_price;
        return $this->attributes['delivery_price'] ? ($this->attributes['delivery_price'] . " " . $this->warehouse->currency_with_label) : $this->warehouse->calculateDeliveryPrice($weight, $this->attributes['weight_type'], $this->attributes['width'], $this->attributes['height'], $this->attributes['length'], $this->attributes['length_type'], true, 0, $azerpoct, $city_id, $additionalDeliveryPrice);
    }

    public function getDeliveryPriceDiscountWithLabelAttribute()
    {
        if (!$this->warehouse) {
            return '-';
        }
        if (!$this->warehouse) {
            return '-';
        }

        return $this->delivery_price_discount . " " . $this->warehouse->currency_with_label;
        $user = User::find($this->attributes['user_id']);
        $azerpoct = 0;
        $city_id = 0;
        if ($user) {
            $azerpoct = $user->azerpoct_send;
            $city_id = $user->city_id;
        }
        $weight = $this->attributes['weight_goods'];
        if (empty($weight))
            $weight = $this->attributes['weight'];

        $additionalDeliveryPrice = $this->additional_delivery_final_price + $this->battery_price + $this->insurance_price;
        return $this->delivery_price_discount ? ($this->delivery_price_discount . " " . $this->warehouse->currency_with_label) : $this->warehouse->calculateDeliveryPrice($weight, $this->attributes['weight_type'], $this->attributes['width'], $this->attributes['height'], $this->attributes['length'], $this->attributes['length_type'], true, $this->discount_percent, $azerpoct, $city_id, $additionalDeliveryPrice);
    }

    public function getTotalPriceAttribute()
    {
        $shippingAmount = $this->getShippingAmountUSD();
        $deliveryAmount = $this->delivery_usd_price_discount;

        return round($shippingAmount + $deliveryAmount, 2);
    }

    public function getTotalPriceCustomsAttribute()
    {
        $deliveryAmount = $this->delivery_usd_price_discount;
        if ($this->costUSD) {
            $deliveryAmount = 0;
            $shippingAmount = $this->costUSD;
        } else
            $shippingAmount = $this->getShippingAmountUSD();

        return round($shippingAmount + $deliveryAmount, 2);
    }

    public function getDiscountPercentWithLabelAttribute()
    {
        $discountPercent = $this->discount_percent;
        if (empty($discountPercent))
            return '-';
        return $discountPercent . "%";
    }

    public function getPromoDiscountWithLabelAttribute()
    {
        $discountStr = '';
        $discountPercent = $this->promo_discount_percent;
        $discountAmount = $this->promo_discount_amount_azn;
        $discountWeight = $this->promo_discount_weight;
        $discountWeightAmount = $this->promo_discount_weight_amount_azn;
        if (!empty($discountWeight) && $discountWeight > 0) {
            $discountStr = $discountWeight . "kg";
        }
        if (!empty($discountWeightAmount) && $discountWeightAmount > 0) {
            $discountStr .= ' (' . $discountWeightAmount . "₼)";
        }
        if (!empty($discountAmount) && $discountAmount > 0) {
            if (!empty($discountStr))
                $discountStr .= '-';
            $discountStr .= $discountAmount . "₼";
        }
        if (!empty($discountPercent) && $discountPercent > 0) {
            if (!empty($discountStr))
                $discountStr .= '-';
            $discountStr .= $discountPercent . "%";
        }
        if (empty($discountStr))
            return '-';
        return $discountStr;
    }

    public function getUlduzumDiscountPercentWithLabelAttribute()
    {
        $discountPercent = $this->ulduzum_discount_percent;
        if (empty($discountPercent))
            return '-';
        return $discountPercent . "%";
    }

    public function getShortTypeAttribute()
    {
        //return str_limit($this->detailed_type,12);
        return str_limit($this->detailed_type_one, 17);
        //return str_limit($this->detailed_type_first,17);
    }

    public function getTotalPriceWithLabelAttribute()
    {
        $shippingAmount = $this->getShippingAmountUSD();
        $deliveryAmount = $this->delivery_usd_price_discount;

        return "$" . round($shippingAmount + $deliveryAmount, 2);
    }

    public function getWebSiteLogoAttribute()
    {
        $domain = getDomain($this->attributes['website_name']);
        $domain = $domain ?: strtolower(str_replace(" ", "", $this->attributes['website_name'])) . ".com";

        return $this->attributes['website_name'] ? 'http://logo.clearbit.com/' . $domain : null;
    }

    public function saveGoodsFromRequest(Request $request)
    {
        if (!$request->has('pkg_goods') || $request->get('pkg_goods') == null || $request->get('pkg_goods') == 1) {
            return;
        }
        PackageGood::where('package_id', $this->id)->delete();
        if ($this->warehouse_id == 12 && $request->has('ru_types') && $request->get('ru_types') != null && is_array($request->get('ru_types')) && count($request->get('ru_types')) > 0) {
            $total_shipping_amount = 0;
            $total_number_items = 0;
            //$total_weight = 0;
            $detailedType = [];
            foreach ($request->get('ru_types') as $key => $type) {
                $hs_code = '';
                if ($request->has('ru_hscodes') && $request->get('ru_hscodes') != null)
                    $hs_code = $request->get('ru_hscodes')[$key];
                $name_ru = '';
                if ($request->has('ru_names') && $request->get('ru_names') != null)
                    $name_ru = $request->get('ru_names')[$key];
                $shipping_amount = $request->get('ru_shipping_amounts')[$key];
                //$weight = 0;
                //if ($request->has('ru_weights') && $request->get('ru_weights') != null)
                //    $weight = $request->get('ru_weights')[$key];
                $number_items = $request->get('ru_items')[$key];
                $total_shipping_amount += $shipping_amount;//*$number_items;
                $total_number_items += $number_items;
                //$total_weight += $weight;//*$number_items;
                $typeName = '';
                $ruType = null;
                if (!empty($hs_code)) {
                    $ruType = RuType::where('hs_code', $hs_code)->where('name_ru', $name_ru)->first();
                    if (!$ruType) {
                        $ruType = new RuType();
                        $ruType->hs_code = $hs_code;
                        $ruType->name_ru = $name_ru;
                        $ruType->save();
                    }
                    if ($ruType)
                        $type = $ruType->id;
                }
                if (!$ruType) {
                    if (!empty($type))
                        $ruType = RuType::find($type);
                }
                if ($ruType)
                    $typeName = $ruType->name_ru;
                $detailedType[] = $number_items . " x " . $typeName;
                $packageGood = new PackageGood;
                $packageGood->package_id = $this->id;
                $packageGood->number_items = $number_items;
                //$packageGood->weight = $weight;
                $packageGood->ru_type_id = $type;
                $packageGood->shipping_amount = $shipping_amount;
                $packageGood->shipping_amount_cur = $this->shipping_amount_cur;
                $packageGood->country_id = $this->country_id;
                $packageGood->warehouse_id = $this->warehouse_id;
                $packageGood->save();
            }

            $this->shipping_amount_goods = $total_shipping_amount;
            $this->number_items_goods = $total_number_items;
            //$this->weight_goods = $total_weight;
            $this->detailed_type = implode("; ", $detailedType);

            if (empty($this->shipping_amount_goods) && !empty($this->shipping_amount)) $this->shipping_amount_goods = $this->shipping_amount;
            if (empty($this->number_items_goods) && !empty($this->number_items)) $this->number_items_goods = $this->number_items;
            //if (empty($this->weight_goods) && !empty($this->weight)) $this->weight_goods = $this->weight;

            //if((!empty($this->shipping_amount_goods) && $this->shipping_amount_goods>0) && (empty($this->shipping_amount) || $this->shipping_amount<=0)) $this->shipping_amount=$this->shipping_amount_goods;
            //if((!empty($this->number_items_goods) && $this->number_items_goods>0) && (empty($this->number_items) || $this->number_items<=0)) $this->number_items=$this->number_items_goods;
            //if((!empty($this->weight_goods) && $this->weight_goods>0) && (empty($this->weight) || $this->weight<=0)) $this->weight=$this->weight_goods;

            $this->use_goods = 1;
            $this->do_use_goods = true;
            $this->save();
        }

        if ($this->warehouse_id != 12 && $request->has('customs_types') && $request->get('customs_types') != null && is_array($request->get('customs_types')) && count($request->get('customs_types')) > 0) {
            $total_shipping_amount = 0;
            $total_number_items = 0;
            $total_weight = 0;
            $detailedType = [];
            if (!(count($request->get('customs_types')) == 1
                && $request->has('ru_weights') && $request->get('ru_weights') != null && is_array($request->get('ru_weights')) && count($request->get('ru_weights')) > 0
                && empty($request->get('customs_types')[0]) && empty($request->get('ru_weights')[0]) && empty($request->get('ru_items')[0]) && empty($request->get('ru_shipping_amounts')[0]))) {
                foreach ($request->get('customs_types') as $key => $type) {
                    //$name_ase = $request->get('ase_names')[$key];
                    $shipping_amount = $request->get('ru_shipping_amounts')[$key];
                    $weight = 0;
                    if ($request->has('ru_weights') && $request->get('ru_weights') != null && is_array($request->get('ru_weights')) && count($request->get('ru_weights')) > 0)
                        $weight = $request->get('ru_weights')[$key];
                    $number_items = $request->get('ru_items')[$key];
                    $customs_type_id = $request->get('customs_types')[$key];
                    $customs_type_parent_id = $request->get('customs_type_parents')[$key];
                    $total_shipping_amount += $shipping_amount;//*$number_items;
                    $total_number_items += $number_items;
                    $total_weight += $weight;//*$number_items;
                    $typeName = '-';//$name_ase;
                    $aseType = null;
                    $customsType = CustomsType::find($customs_type_id);
                    if ($customsType)
                        $typeName = $customsType->name_en_with_parent;
                    $detailedType[] = $number_items . " x " . $typeName;
                    $packageGood = new PackageGood;
                    $packageGood->package_id = $this->id;
                    $packageGood->number_items = $number_items;
                    $packageGood->weight = $weight;
                    $packageGood->customs_type_id = $customs_type_id;
                    $packageGood->customs_type_parent_id = $customs_type_parent_id;
                    $packageGood->shipping_amount = $shipping_amount;
                    $packageGood->shipping_amount_cur = $this->shipping_amount_cur;
                    $packageGood->country_id = $this->country_id;
                    $packageGood->warehouse_id = $this->warehouse_id;
                    $packageGood->save();
                }

                $this->shipping_amount_goods = $total_shipping_amount;
                $this->number_items_goods = $total_number_items;
                $this->weight_goods = $total_weight;
                $this->detailed_type = implode("; ", $detailedType);

                if (empty($this->shipping_amount_goods) && !empty($this->shipping_amount)) $this->shipping_amount_goods = $this->shipping_amount;
                if (empty($this->number_items_goods) && !empty($this->number_items)) $this->number_items_goods = $this->number_items;
                if (empty($this->weight_goods) && !empty($this->weight)) $this->weight_goods = $this->weight;

                //if((!empty($this->shipping_amount_goods) && $this->shipping_amount_goods>0) && (empty($this->shipping_amount) || $this->shipping_amount<=0)) $this->shipping_amount=$this->shipping_amount_goods;
                //if((!empty($this->number_items_goods) && $this->number_items_goods>0) && (empty($this->number_items) || $this->number_items<=0)) $this->number_items=$this->number_items_goods;
                //if((!empty($this->weight_goods) && $this->weight_goods>0) && (empty($this->weight) || $this->weight<=0)) $this->weight=$this->weight_goods;

                $this->use_goods = 1;
                $this->do_use_goods = true;
                $this->save();
            }
        }
    }

    public function getParcelNameAttribute()
    {
        return $this->parcel && $this->parcel->count() ? $this->parcel->first()->custom_id : 'No';
    }

    public function getBagNameAttribute()
    {
        return $this->bag && $this->bag->count() ? $this->bag->first()->custom_id : 'No';
    }

    public function getBarcodeImage($text, $dir = '')
    {
        $file_gif = '/barcode/' . $text . '.gif';
        $file_jpg = '/barcode/' . $text . '.jpg';
        $a_url = '?tm=' . time();
        if (!empty($dir)) {
            $file_gif = '/barcode/' . $dir . '/' . $text . '.gif';
            $file_jpg = '/barcode/' . $dir . '/' . $text . '.jpg';
        }
        if (file_exists(public_path() . $file_gif))
            return env('APP_URL') . $file_gif . $a_url;

        if (file_exists(public_path() . $file_jpg))
            return env('APP_URL') . $file_jpg . $a_url;
        $url = 'https://barcode.tec-it.com/barcode.ashx?data=' . $text . '&code=Code128&dpi=300&dataseparator=""';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch);
        if (!curl_errno($ch) && $status['http_code'] == 200) {
            curl_close($ch);
            $fp = fopen(public_path() . $file_gif, 'x');
            fwrite($fp, $raw);
            fclose($fp);
            //if(filesize(public_path() . $file_gif) != 27561)
            return env('APP_URL') . $file_gif . $a_url;
        }
        $url = 'https://www.cognex.com/api/Sitecore/Barcode/Get?data=' . $text . '&code=BCL_CODE128&width=600&imageType=PNG&foreColor=%23000000&backColor=%23FFFFFF&rotation=RotateNoneFlipNone';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $raw = curl_exec($ch);
        $status = curl_getinfo($ch);
        if (!curl_errno($ch) && $status['http_code'] == 200) {
            curl_close($ch);
            $fp = fopen(public_path() . $file_jpg, 'x');
            fwrite($fp, $raw);
            fclose($fp);
            return env('APP_URL') . $file_jpg;
        }
        curl_close($ch);
        return "";
    }

    /* * * * * *
     * Setters *
     * * * * */

    public function setWarehouseIdAttribute($id)
    {
        $this->attributes['warehouse_id'] = $id;
    }

    public static function generateCustomId($digits = 13)
    {
        do {

            $code = env('MEMBER_PREFIX_CODE', 'ASE') . str_pad(rand(0, pow(10, $digits) - 1), $digits, '0', STR_PAD_LEFT);

            $check = Package::whereCustomId($code)->first();
            if (!$check) {
                break;
            }
        } while (true);

        return $code;
    }

    public function getUnknownStatusAtAttribute($value)
    {
        if ($value == '0000-00-00 00:00:00') return '';
        return $value;
    }


    /* * * * * *
     * Scopes *
     * * * * */

    public function scopeDone($query)
    {
        return $query->where('status', 3);
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->whereIn('paid_for', [
            'PACKAGE_BALANCE',
            'PACKAGE',
        ])->latest();
    }

    public function transactionDebt()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->where('paid_for', 'PACKAGE_DEBT')->latest();
    }
    public function transactionBroker()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->where('paid_for', 'PACKAGE_BROKER')->latest();
    }

    public function getPaidByAttribute()
    {
        return $this->attributes['paid'] ? ($this->transaction ? $this->transaction->paid_by : '-') : "-";
    }

    public function getBrokerPaidByAttribute()
    {
        return $this->attributes['paid_broker'] ? ($this->transactionBroker ? $this->transactionBroker->paid_by : '-') : "-";
    }

    protected static function boot()
    {
        parent::boot();

        // auto-sets values on creation
        static::creating(function ($query) {

            //tracking code yoxlamasi
            $trackingCode = $query->tracking_code;

            if (!$trackingCode) {
                throw new \Exception("tracking_code yoxdur");
            }

            $exists = Package::where('tracking_code', $trackingCode)->lockForUpdate()->exists();
            if ($exists) {
                throw new \Exception("Bu tracking_code artıq mövcuddur, ikinciyi qoymaq olmaz!");
            }

            $user = null;
            if (request()->has('user_id'))
                $user = User::find(request()->get('user_id'));
            $azerpoct = 0;
            $city_id = 0;
            if ($user) {
                $azerpoct = $user->azerpoct_send;
                $city_id = $user->city_id;
            }
            $query->custom_id = $query->custom_id ?: self::generateCustomId();

            $webSiteName = getOnlyDomainWithExt($query->website_name);
            $query->website_name = $webSiteName ?: $query->website_name;

            $type_id = $query->type_id;
            $customs_type_id = null;
            if (isset($query->customs_type_id))
                $customs_type_id = $query->customs_type_id;
            $number_items = $query->number_items;

            if (!empty($number_items) && !empty($customs_type_id)) {
                $customsType = CustomsType::find($customs_type_id);
                if ($customsType)
                    $query->detailed_type = $number_items . ' x ' . $customsType->name_en_with_parent;
            } else if (!empty($number_items) && !empty($type_id)) {
                $type = PackageType::find($type_id);
                if ($type)
                    $query->detailed_type = $number_items . ' x ' . $type->translateOrDefault('en')->name;
            }

            //if ($query->country_id and ! $query->warehouse_id) {
            if ($query->country_id || $query->warehouse_id) {
                $warehouse = null;
                if ($query->country_id)
                    $warehouse = Warehouse::whereCountryId($query->country_id)->latest()->first();
                else if ($query->warehouse_id)
                    $warehouse = Warehouse::where('id', $query->warehouse_id)->latest()->first();

                if ($warehouse) {
                    $query->warehouse_id = $warehouse->id;
                    $weight = $query->weight_goods;
                    $curShippingAmount = Package::s_getShippingAmountUSD($query);
                    if (empty($weight))
                        $weight = $query->weight;
                    $weight_type = $query->weight_type;
                    if (!$weight_type) $weight_type = 0;
                    $length_type = $query->length_type;
                    if (!$length_type) $length_type = 0;
                    if ($weight && !request()->has('delivery_price') && request()->get('name') != 'delivery_price') {

                        $additionalDeliveryPrice = 0;

                        $additional_delivery_final_price = 0;
                        if (isset($query->additional_delivery_price) && $query->additional_delivery_price && $query->additional_delivery_price > 0 && $warehouse->use_additional_delivery_price)
                            $additional_delivery_final_price = $query->additional_delivery_price * 1.2;
                        $query->additional_delivery_final_price = $additional_delivery_final_price;
                        $additionalDeliveryPrice += $additional_delivery_final_price;

                        $battery_price = 0;
                        if (isset($query->has_battery) && $query->has_battery && $warehouse->battery_price && $warehouse->battery_price > 0)
                            $battery_price = $warehouse->battery_price;
                        $query->battery_price = $battery_price;
                        $additionalDeliveryPrice += $battery_price;

                        $insurance_price = 0;
                        if ($curShippingAmount && isset($query->has_insurance) && $query->has_insurance)
                            $insurance_price = $curShippingAmount * 0.01;
                        $query->insurance_price = $insurance_price;
                        $additionalDeliveryPrice += $insurance_price;

                        $deliveryPrice = $warehouse->calculateDeliveryPrice2($weight, $weight_type, $query->width, $query->height, $query->length, $length_type, false, 0, $azerpoct, $city_id, $additionalDeliveryPrice, $query->custom_id);
                        $query->delivery_price = $deliveryPrice;
                    }
                }
            }

            if ($query->warehouse_id && in_array($query->status, [0, 1, 2])) {
                $cdate = Carbon::now();
                $discounts = Discount::where('warehouse_id', $query->warehouse_id)->where('is_active', 1)->where('start_at', '<=', $cdate)->where('stop_at', '>=', $cdate)->get();
                $discountPercent = 0;
                foreach ($discounts as $discount) {
                    $discountPercent += $discount->percent;
                }
                if ($discountPercent > 0) {
                    if ($discountPercent > 100)
                        $discountPercent = 100;
                    $query->discount_percent = $discountPercent;
                    $query->discount_at = $cdate;
                }
            }

            if (empty($query->shipping_amount_goods) && !empty($query->shipping_amount)) $query->shipping_amount_goods = $query->shipping_amount;
            if (empty($query->number_items_goods) && !empty($query->number_items)) $query->number_items_goods = $query->number_items;
            if (empty($query->weight_goods) && !empty($query->weight)) $query->weight_goods = $query->weight;
            if ($query->do_use_goods != null && !$query->do_use_goods)
                $query->use_goods = 0;
        });

        static::updating(function ($query) {

            $_package = Package::withTrashed()->find($query->id);
            $user = null;
            if ($_package->user_id)
                $user = User::find($_package->user_id);
            $azerpoct = 0;
            $city_id = 0;
            if ($user) {
                $azerpoct = $user->azerpoct_send;
                $city_id = $user->city_id;
            }

            if ($_package && $_package->user_id && isset($query->user_id) && $query->user_id && $_package->user_id != $query->user_id) {
                //file_put_contents('/var/log/ase_user_change.log', $_package->tracking_code . ' user change ' . $_package->user_id . ' => ' . $query->user_id . "\n", FILE_APPEND);
                if ($_package->ukr_express_id && in_array($_package->status, [0, 1, 6])) {
                    if ($_package->ukr_express_pd || $_package->ukr_express_dec) {
                        //file_put_contents('/var/log/ase_user_change.log', '  UExpress cleared packing data' . "\n", FILE_APPEND);
                        $query->ukr_express_pd = 0;
                        $query->ukr_express_dec = 0;
                    }
                }
            }

            //Debt
            if ($_package->status != 4 and $query->status == 4) {
                $query->customs_at = now();
            }
            //EndDebt

            if ($query->warehouse_id) {
                if (env('APP_ENV') != "local") {
                    file_put_contents('/var/log/ase_delivery_price.log', $query->id . ' delivery price ', FILE_APPEND);
                }
                $warehouse = Warehouse::find($query->warehouse_id);
                $curWeightGoods = $query->weight_goods;
                $curWeight = $query->weight;
                $delPrice = $query->delivery_price;
                $hasBattery = $query->has_battery;
                $curShippingAmount = Package::s_getShippingAmountUSD($query);
                $prevWeight = null;
                $prevWeightGoods = null;
                $prevShippingAmount = null;
                $prevHasBattery = null;
                if ($_package) {
                    $prevWeight = $_package->weight;
                    $prevWeightGoods = $_package->weight_goods;
                    $prevHasBattery = $_package->has_battery;
                    $prevShippingAmount = $_package->getShippingAmountUSD();
                }
                $weight = $curWeight;
                if (empty($weight) || (($curWeight == $prevWeight) && ($curWeightGoods != $prevWeightGoods)))
                    $weight = $curWeightGoods;
                if (env('APP_ENV') != "local") {
                    file_put_contents('/var/log/ase_delivery_price.log', 'weight: ' . $weight . ' prev : (' . $prevWeight . ',' . $prevWeightGoods . ',' . $prevHasBattery . ')' . ' cur : (' . $curWeight . ',' . $curWeightGoods . ',' . $hasBattery . ')', FILE_APPEND);
                }

//                dd($warehouse, $weight, $delPrice, $curWeight, $prevWeight,$curWeightGoods,$prevWeightGoods,$hasBattery,$prevHasBattery,$prevShippingAmount,$curShippingAmount);

//                dd($warehouse && $weight && (empty($delPrice) || ($curWeight != $prevWeight) || ($curWeightGoods != $prevWeightGoods) || ($curShippingAmount != $prevShippingAmount) || ($hasBattery != $prevHasBattery && $warehouse->battery_price && $warehouse->battery_price > 0)));

                //if ($warehouse && $weight && (empty($delPrice) || ($weight != $prevWeight))) {
                if ($warehouse && $weight && (empty($delPrice) || ($curWeight != $prevWeight) || ($curWeightGoods != $prevWeightGoods) || ($curShippingAmount != $prevShippingAmount) || ($hasBattery != $prevHasBattery && $warehouse->battery_price && $warehouse->battery_price > 0))) {
                    $additionalDeliveryPrice = 0;

                    $additional_delivery_final_price = 0;
                    if (isset($query->additional_delivery_price) && $query->additional_delivery_price && $query->additional_delivery_price > 0 && $warehouse->use_additional_delivery_price)
                        $additional_delivery_final_price = $query->additional_delivery_price * 1.2;
                    $query->additional_delivery_final_price = $additional_delivery_final_price;
                    $additionalDeliveryPrice += $additional_delivery_final_price;

                    $battery_price = 0;
                    if (isset($query->has_battery) && $query->has_battery && $warehouse->battery_price && $warehouse->battery_price > 0)
                        $battery_price = $warehouse->battery_price;
                    $query->battery_price = $battery_price;
                    $additionalDeliveryPrice += $battery_price;

                    $insurance_price = 0;
                    if ($curShippingAmount && isset($query->has_insurance) && $query->has_insurance)
                        $insurance_price = $curShippingAmount * 0.01;
                    $query->insurance_price = $insurance_price;
                    $additionalDeliveryPrice += $insurance_price;

                    $deliveryPrice = $warehouse->calculateDeliveryPrice2($weight, $query->weight_type, $query->width, $query->height, $query->length, $query->length_type, false, 0, $azerpoct, $city_id, $additionalDeliveryPrice, $query->custom_id);;

                    $query->delivery_price = $deliveryPrice;
                    if (env('APP_ENV') != "local") {
                        file_put_contents('/var/log/ase_delivery_price.log', " changed(" . $weight . "=" . $deliveryPrice . ")\n", FILE_APPEND);
                    }
                } else {
                    if (env('APP_ENV') != "local") {
                        file_put_contents('/var/log/ase_delivery_price.log', "\n", FILE_APPEND);
                    }
                }
            }


            if (!$query->delivery_price) {
                $query->paid = 0;
            }

            if ($query->status == 1 && !$query->sent_at) {
                $query->sent_at = Carbon::now();
            }


            $type_id = $query->type_id;
            $customs_type_id = null;
            if (isset($query->customs_type_id))
                $customs_type_id = $query->customs_type_id;
            $number_items = $query->number_items;
            $detailed_type = $query->detailed_type;
            if ($_package && ($type_id != $_package->type_id || $number_items != $_package->number_items)) {
                $type = PackageType::find($type_id);
                if ($type)
                    $query->detailed_type = $number_items . ' x ' . $type->translateOrDefault('en')->name;
            }
            if ($_package && ($customs_type_id != $_package->customs_type_id || $number_items != $_package->number_items)) {
                $customsType = CustomsType::find($customs_type_id);
                if ($customsType)
                    $query->detailed_type = $number_items . ' x ' . $customsType->name_en_with_parent;
            }

            if ($query->warehouse_id && in_array($query->status, [0, 1, 2])) {
                if (($_package && (
                        (($query->status != $_package->status) && empty($_package->discount_percent))
                        ))
                    || (!$_package)
                ) {
                    $cdate = Carbon::now();
                    $discounts = Discount::where('warehouse_id', $query->warehouse_id)->where('is_active', 1)->where('start_at', '<=', $cdate)->where('stop_at', '>=', $cdate)->get();
                    $discountPercent = 0;
                    foreach ($discounts as $discount) {
                        $discountPercent += $discount->percent;
                    }
                    if ($discountPercent > 0) {
                        if ($discountPercent > 100)
                            $discountPercent = 100;
                        $query->discount_percent = $discountPercent;
                        $query->discount_at = $cdate;
                    } else {
                        $query->discount_percent = null;
                        $query->discount_at = null;
                    }
                }
            }

            if ($query->status == 6) {
                $query->discount_percent = null;
                $query->discount_at = null;
            }

            $webSiteName = getOnlyDomainWithExt($query->website_name);
            $query->website_name = $webSiteName ?: $query->website_name;

            if (empty($query->shipping_amount_goods) && empty($_package->shipping_amount_goods) && !empty($query->shipping_amount)) $query->shipping_amount_goods = $query->shipping_amount;
            if (empty($query->number_items_goods) && empty($_package->number_items_goods) && !empty($query->number_items)) $query->number_items_goods = $query->number_items;
            if (empty($query->weight_goods) && empty($_package->weight_goods) && !empty($query->weight)) $query->weight_goods = $query->weight;

            if ($query->do_use_goods != null && !$query->do_use_goods)
                $query->use_goods = 0;
        });


    }

    public static function getCarrier($trackingNumber)
    {
        $carrier = null;

        $matchUPS1 = '/\b(1Z ?[0-9A-Z]{3} ?[0-9A-Z]{3} ?[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]|[\dT]\d\d\d ?\d\d\d\d ?\d\d\d)\b/';
        $matchUPS2 = '/^[kKJj]{1}[0-9]{10}$/';

        $matchUSPS0 = '/(\b\d{30}\b)|(\b91\d+\b)|(\b\d{20}\b)/';
        $matchUSPS1 = '/(\b\d{30}\b)|(\b91\d+\b)|(\b\d{20}\b)|(\b\d{26}\b)| ^E\D{1}\d{9}\D{2}$|^9\d{15,21}$| ^91[0-9]+$| ^[A-Za-z]{2}[0-9]+US$/i';
        $matchUSPS2 = '/^E\D{1}\d{9}\D{2}$|^9\d{15,21}$/';
        $matchUSPS3 = '/^91[0-9]+$/';
        $matchUSPS4 = '/^[A-Za-z]{2}[0-9]+US$/';
        $matchUSPS5 = '/(\b\d{30}\b)|(\b91\d+\b)|(\b\d{20}\b)|(\b\d{26}\b)| ^E\D{1}\d{9}\D{2}$|^9\d{15,21}$| ^91[0-9]+$| ^[A-Za-z]{2}[0-9]+US$/i';

        $matchFedex1 = '/(\b96\d{20}\b)|(\b\d{15}\b)|(\b\d{12}\b)/';
        $matchFedex2 = '/\b((98\d\d\d\d\d?\d\d\d\d|98\d\d) ?\d\d\d\d ?\d\d\d\d( ?\d\d\d)?)\b/';
        $matchFedex3 = '/^[0-9]{15}$/';

        if (preg_match($matchUPS1, $trackingNumber) || preg_match($matchUPS2, $trackingNumber)) {
            $carrier = 'UPS';
        } else {
            if (preg_match($matchUSPS0, $trackingNumber) || preg_match($matchUSPS1, $trackingNumber) || preg_match($matchUSPS2, $trackingNumber) || preg_match($matchUSPS3, $trackingNumber) || preg_match($matchUSPS4, $trackingNumber) || preg_match($matchUSPS5, $trackingNumber)) {

                $carrier = 'USPS';
            } else {
                if (preg_match($matchFedex1, $trackingNumber) || preg_match($matchFedex2, $trackingNumber) || preg_match($matchFedex3, $trackingNumber)) {

                    $carrier = 'FedEx';
                } else {
                    if (0) {
                        $carrier = 'DHL';
                    }
                }
            }
        }

        return $carrier;
    }

    public function scopeLikeTracking($query, $code)
    {
        return $query->where('tracking_code', 'like', '%' . $code . "%");
    }

    public function scopeIncustoms($query, $st = 2)
    {
        $codeConditions = [200, 400];

        switch ($st) {
            case 2:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->whereNotNull('package_carriers.ecoM_REGNUMBER');
            case 4:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->whereNull('package_carriers.ecoM_REGNUMBER');
            case 3:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->whereNotNull('package_carriers.depesH_NUMBER');
            case 5:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->whereNull('package_carriers.depesH_NUMBER');
            case 6:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->where('package_carriers.status', '>=', 1);
            case 7:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->where('package_carriers.status', 0);
            case 8:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->where('package_carriers.status', 1)->whereNull('package_carriers.ecoM_REGNUMBER');
            case 9:
                return $query->whereIn('package_carriers.code', $codeConditions)
                    ->where('package_carriers.status', 1)->whereNull('package_carriers.depesH_NUMBER');
            case 1:
                return $query->whereIn('package_carriers.code', $codeConditions);
            case 0:
                return $query->whereNull('package_carriers.id');
            case 10:
                return $query->whereNotNull('package_carriers.id')
                    ->whereNotIn('package_carriers.code', $codeConditions);
            default:
                return $query;
        }
    }

    public function scopePaidstr($query, $paidStr)
    {
        return $query->where('transactions.type', 'OUT')->whereIn('transactions.paid_for', [
            'PACKAGE_BALANCE',
            'PACKAGE',
        ])->where('transactions.paid_by', $paidStr);
    }

    public function scopeReady($query)
    {
        return $query->whereNotNull('number_items_goods')->whereNotNull('shipping_amount_goods')->where('shipping_amount_goods', '>', 0)->whereNotNull('weight_goods')->doesntHave('parcel');
    }

    public function getIsInCustomsAttribute()
    {
        $carrier = $this->carrier;
        if (!$carrier) return false;
        if ($carrier->is_commercial) return true;
        if ($carrier->ecoM_REGNUMBER) return true;
        return false;
    }

    public function getIsReadyAttribute()
    {
        $weight = $this->attributes['weight_goods'];
        if (empty($weight))
            $weight = $this->attributes['weight'];
        $shipping_amount = $this->attributes['shipping_amount_goods'];
        if (empty($shipping_amount))
            $shipping_amount = $this->attributes['shipping_amount'];
        return $this->attributes['warehouse_id'] != null && $this->attributes['user_id'] != null && $shipping_amount != null && $shipping_amount > 0 && $weight != null;
    }

    public function getWebsiteNameAttribute($value)
    {
        $webSiteName = getOnlyDomainWithExt($value);

        return $webSiteName ?: ($value ? strtolower($value) : $value);
    }

    public function getTrackingCodeAttribute($value)
    {
        if ($value)
            return str_replace("\xE2\x80\x8B", "", $value);
        else
            return "E" . (6005710000 + $this->id);
    }

    public function getAlertAttribute()
    {
        return ($this->attributes['invoice'] == null && $this->attributes['dec_message'] == 3) ? 1 : 0;
    }

    public function getNetWeightAttribute()
    {
        $weight = $this->attributes['weight_goods'];
        if (empty($weight))
            $weight = $this->attributes['weight'];
        $weightUnit = $this->attributes['weight_type'];
        $weight = $weight * config('ase.attributes.weightConvert')[$weightUnit];
        $size_index = $this->volume_weight;

        return $size_index > $weight ? $size_index : $weight;
    }

    public function getVolumeWeightAttribute()
    {
        $width = $this->attributes['width'];
        $height = $this->attributes['height'];
        $length = $this->attributes['length'];
        $sizeUnit = $this->attributes['length_type'];

        if ($width) {
            $width = $width * config('ase.attributes.lengthConvert')[$sizeUnit];
        }
        if ($height) {
            $height = $height * config('ase.attributes.lengthConvert')[$sizeUnit];
        }
        if ($length) {
            $length = $length * config('ase.attributes.lengthConvert')[$sizeUnit];
        }

        $country = $this->defaultCountry();

        $size_index = $country && $country->delivery_index ? ($width * $height * $length / $country->delivery_index) : 0;

        return $size_index;
    }

    public function getDetailedType1Attribute()
    {
        $slen = 48;
        $str = $this->detailed_type . ($this->other_type ? "(" . $this->other_type . ")" : null);
        $len = strlen($str);
        $arr = [];
        if ($len >= $slen)
            return substr($str, 0, $slen);
        return $str;
    }

    public function getDetailedType2Attribute()
    {
        $slen = 48;
        $str = $this->detailed_type . ($this->other_type ? "(" . $this->other_type . ")" : null);
        $len = strlen($str);
        $arr = [];
        if ($len >= 2 * $slen)
            return substr($str, $slen, $slen);
        if ($len >= $slen && $len < 2 * $slen)
            return substr($str, $slen, $len);
        return "";
    }

    public function getDetailedType3Attribute()
    {
        $slen = 48;
        $str = $this->detailed_type . ($this->other_type ? "(" . $this->other_type . ")" : null);
        $len = strlen($str);
        $arr = [];
        if ($len >= 2 * $slen)
            return substr($str, 2 * $slen, $len);
        return "";
    }

    public function getDetailedTypeNumberAttribute()
    {
        $number_items = 0;
        if ($this->goods && count($this->goods) > 0) {
            foreach ($this->goods as $good) {
                $number_items += $good->number_items;
            }
            return $number_items;
        }
        $number_items = 1;
        if (array_key_exists('number_items_goods', $this->attributes))
            $number_items = $this->attributes['number_items_goods'];
        if (empty($number_items) && array_key_exists('number_items', $this->attributes))
            $number_items = $this->attributes['number_items'];
        return $number_items;
    }

    public function getDetailedTypeParentNameAttribute()
    {
        if ($this->goods && count($this->goods) > 0) {
            return $this->goods[0]->name_parent;
        }
        if ($this->type)
            return $this->type->translateOrDefault('en')->name;
        return "-";
    }


    public function generateHtmlInvoice($remove_old = false, $invoice1 = NULL, $debug = false)
    {
        $invoice_url = $invoice1;
        if (!$invoice_url)
            $invoice_url = $this->invoice;
        if (!$invoice_url)
            $invoice_url = '/no_image.jpg';
        if (!$invoice_url)
            return '';
        $root_dir = '/var/www/ase/public';
        $invoice = str_replace(ENV('APP_URL'), '', $invoice_url);
        $ext = pathinfo($invoice, PATHINFO_EXTENSION);
        $html_invoice = str_replace('.' . $ext, '.html', $invoice);
        $invoice_file = $root_dir . $invoice;
        $html_invoice_file = $root_dir . $html_invoice;
        $html_invoice_url = ENV('APP_URL') . $html_invoice;
        echo $html_invoice_file . "\n";
        if (file_exists($html_invoice_file)) {
            if ($debug) echo "Exists\n";
            if ($remove_old) {
                echo "Removing " . $html_invoice_file . "\n";
                unlink($html_invoice_file);
            } else {
                return $html_invoice_url;
            }
        }
        if (!file_exists($invoice_file)) {
            if ($debug)
                echo "Not Exists" . $invoice_file . "\n";
            return '';
        }
        /*if(strtolower($ext)=='docx') {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($invoice_file);
            $htmlWriter = new \PhpOffice\PhpWord\Writer\HTML($phpWord);
            $htmlWriter->save($html_invoice_file);
	    return $html_invoice_url;
	}*/
        if (strtolower($ext) == 'docx') {
            $pdf_invoice = str_replace('.' . $ext, '.pdf', $invoice);
            $pdf_invoice_file = $root_dir . $pdf_invoice;
            $pdf_invoice_url = ENV('APP_URL') . $pdf_invoice;
            $cmd = "unoconv -f pdf -o " . $pdf_invoice_file . " " . $invoice_file;
            passthru($cmd, $ret);
            if ($ret == 0) {
                if (file_exists($pdf_invoice_file)) {
                    if ($debug) {
                        echo "pdf ok: " . $pdf_invoice_url . "\n";
                    }
                    return $this->generateHtmlInvoice($remove_old, $pdf_invoice_url, $debug);
                }
            }
        }
        if (strtolower($ext) == 'pdf') {
            $jpeg_invoice = str_replace('.' . $ext, '.jpeg', $invoice);
            $jpeg_invoice_file = $root_dir . $jpeg_invoice;
            $cmd = "pdftoppm " . $invoice_file . " " . $jpeg_invoice_file . " -jpeg";
            passthru($cmd, $ret);
            if ($ret == 0) {
                //echo "Ok\n";
                $jpeg_invoice_files = $root_dir . str_replace('.' . $ext, '.*.jpg', $invoice);
                $files = glob($jpeg_invoice_files);
                $html = "<html>\n";
                $html .= "  <body>\n";
                $html .= '    <div style="display:inline-block">' . "\n";
                $cnt = 0;
                foreach ($files as $file) {
                    $cnt++;
                    $jpeg_invoice = str_replace($root_dir . '/uploads/packages/', '', $file);
                    $jpeg_invoice = ENV('APP_URL') . '/uploads/packages/' . $jpeg_invoice;
                    //$html.='      <img style="max-width:100%; max-height:100%;" src="'.$jpeg_invoice.'">'."\n";
                    $html .= '      <img width="100%" height="95%" src="' . $jpeg_invoice . '">' . "\n";
                    //echo $jpeg_invoice."\n";
                    if ($cnt >= 1) break; // Put only one picture into html
                }
                $html .= "    </div>\n";
                $html .= "  </body>\n";
                $html .= "</html>\n";
                //echo $html;
                file_put_contents($html_invoice_file, $html);
                if ($debug)
                    echo "html ok: " . $html_invoice_file . "\n";
                return $html_invoice_url;
            }
        }
        if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'jfif'])) {
            $html = "<html>\n";
            $html .= "  <body>\n";
            $html .= '    <div style="display:inline-block">' . "\n";
            $jpeg_invoice = str_replace($root_dir . '/uploads/packages/', '', $invoice_file);
            $jpeg_invoice = ENV('APP_URL') . '/uploads/packages/' . $jpeg_invoice;
            //$html.='      <img style="max-width:100%; max-height:100%;" src="'.$jpeg_invoice.'">'."\n";
            $html .= '      <img width="100%" height="95%" src="' . $jpeg_invoice . '">' . "\n";
            $html .= "    </div>\n";
            $html .= "  </body>\n";
            $html .= "</html>\n";
            file_put_contents($html_invoice_file, $html);
            return $html_invoice_url;
        }
        return '';
    }


    public function getDetailedTypeFirstAttribute()
    {
        if ($this->goods && count($this->goods) > 0) {
            $good = $this->goods[0];
            return $good->name_one;
        }
        if ($this->type)
            return $this->type->translateOrDefault('en')->name;
        return "-";
    }

    public function getDetailedTypeOneAttribute()
    {
        if ($this->goods && count($this->goods) > 0) {
            $str = '';
            $i = 0;
            foreach ($this->goods as $good) {
                if ($i > 0) $str .= "; ";
                $str .= $good->number_items . " x " . $good->name_one;
                $i++;
            }
            return $str;
        }
        $number_items = 0;
        if (array_key_exists('number_items_goods', $this->attributes))
            $number_items = $this->attributes['number_items_goods'];
        if (empty($number_items) && array_key_exists('number_items', $this->attributes))
            $number_items = $this->attributes['number_items'];
        if ($this->type)
            return $number_items . " x " . $this->type->translateOrDefault('en')->name;
        return $number_items . " x " . "-";
    }

    public function getDetailedTypeAttribute($value)
    {
        if ($value)
            return $value;
        if ($this->goods && count($this->goods) > 0) {
            $str = '';
            $i = 0;
            foreach ($this->goods as $good) {
                if ($i > 0) $str .= "; ";
                $str .= $good->number_items . " x " . $good->name;
                $i++;
            }
            return $str;
        }
        $number_items = 0;
        if (array_key_exists('number_items_goods', $this->attributes))
            $number_items = $this->attributes['number_items_goods'];
        if (empty($number_items) && array_key_exists('number_items', $this->attributes))
            $number_items = $this->attributes['number_items'];
        if ($this->type)
            return $number_items . " x " . $this->type->translateOrDefault('en')->name;
        return $number_items . " x " . "-";
    }

    public function getFakeInvoiceIdAttribute()
    {
        if ($this->warehouse && $this->warehouse->country && $this->warehouse->country->code == 'ru') {
            return str_replace('ASE', 'INV', $this->custom_id);
        } else {
            return str_replace("E", "C", $this->tracking_code);
        }
    }

    public function getFakeAddressAttribute()
    {
        $country = $this->defaultCountry();

        return $country ? isset(config('ase.addresses.' . strtolower($country->code))[$this->id % 2]) ? config('ase.addresses.' . strtolower($country->code))[$this->id % 2] : false : false;
    }

    public function getDontDeleteAttribute()
    {
        return auth()->guard('worker')->check() && $this->declaration;
    }

    public function defaultCountry()
    {
        return ($this->warehouse and $this->warehouse->country) ? $this->warehouse->country : ($this->country ? $this->country : null);
    }

    /*
        public function manager()
        {
            return $this->belongsToMany(Worker::class, 'activities', 'content_id', 'worker_id')->whereNotNull('worker_id');
        }

        public function getWorkerAttribute()
        {
            return ($this->manager() && $this->manager()->first()) ? ($this->manager()->first())->name : "-";
        }
     */

    public function addPackageToContainer()
    {

    }

    public function setComment($value)
    {
        return $this->bot_comment = $value;
    }


}
