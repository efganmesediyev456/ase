<?php

namespace App\Models;

use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Azerpost\AzerpostPackage;
use App\Models\Extra\Notification;
use App\Models\Kargomat\KargomatPackage;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Surat\SuratPackage;
use App\Models\YeniPoct\YenipoctPackage;
use App\Services\Package\PackageService;
use App\Traits\ModelEventLogger;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    const STATES = [
        'InBaku' => 16,
        'InKobia' => 20,
        'Done' => 17,
        'Undelivered' => 23,
        'Rejected' => 19,
    ];

    public $uploadDir = 'uploads/packages/';
    public $with = ['carrier', 'partner'];
    public $errorStr = '';

    protected $fillable = [
        'partner_id',
        'container_id',
        'customer_id',
        'city_id',
        'courier_delivery_id',
        'azeriexpress_office_id',
        'azerpost_office_id',
        'surat_office_id',
        'yenipoct_office_id',
        'kargomat_office_id',
        'store_status',
        'warehouse_id',
        'tracking_code',
        'internal_tracking_number',
        'type',
        'status',
        'fin',
        'phone',
        'email',
        'zip_code',
        'address',
        'city_name',
        'region_name',
        'cell',
        'paid',
        'website',
        'number_items',
        'detailed_type',
        'weight',
        'currency',
        'shipping_amount',
        'shipping_amount_cur',
        'delivery_type',
        'delivery_price',
        'delivery_price_cur',
        'delivery_price_status',
        'delivery_price_azn',
        'json_txt',
        'error_txt',
        'comment_txt',
        'bot_comment',
        'product_url',
        'parcel_code',
        'data',
        'fullname',
        'declaration_sms_count',
        'from_country',
        'declaration',
        'debt_price',
        'stop_debt',
        'paid_debt',
        'latitude',
        'longitude',
        'shelf_id',
        'is_meest'
    ];

    protected $dates = [
        'deleted_at',
        'error_at',
        'sent_at',
        'received_at',
        'delivered_at',
        'scanned_at',
        'created_at',
        'updated_at',
    ];

    private $cm;

    protected static function boot()
    {
        parent::boot();
        static::created(function ($query) {
            if ($query->partner_id == 8 && $query->delivery_type == 'HD') {
                if (Track::auto_courier($query)) {
                    $query->save();
                }
            }
        });
        static::updating(function ($query) {
            //Debt
            $_package = Track::withTrashed()->find($query->id);

            if ($_package->status != 18 and $query->status == 18) {
                $query->customs_at = now();
            }

            if ($_package->status == 18 || $_package->status == 45) {

                if ($query->status == 20 && $_package->debt_price > 0 && $_package->paid_debt == 0) {

                    if($query->status == 20 and $query->store_status==2){
                        Notification::sendTrack($_package->id, 'customs_storage_fee',now()->addHours(3));
                    }else{
                        Notification::sendTrack($_package->id, 'customs_storage_fee');
                    }

                    $_package->debt_sms_count += 1;
                    $_package->save();
                }

            }

            if ($_package->status != 45 and $query->status == 45) {
                $query->customs_at = now();
            }
            //EndDebt
        });
    }

    public function getDeliveryPriceAzn1Attribute()
    {
        $cur_str = NULL;
        if (!$this->delivery_price_cur || $this->delivery_price_cur == 1) $cur_str = 'USD';
        else $cur_str = $this->delivery_price_cur;
        return convertToAZN($this->delivery_price, $cur_str);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'id', 'custom_id')->where('paid_for', 'TRACK_DELIVERY')->where('type', 'OUT');
    }
    public function transactionDebt()
    {
        return $this->belongsTo(Transaction::class, 'id', 'custom_id')->where('type', 'OUT')->where('paid_for', 'TRACK_DEBT')->latest();
    }

    public function getPaidByAttribute()
    {
        return $this->attributes['paid'] ? ($this->transaction ? $this->transaction->paid_by : '-') : "-";
    }
    public function transactionBroker()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->where('paid_for', 'TRACK_BROKER')->latest();
    }
    public function getBrokerPaidByAttribute()
    {
        return $this->attributes['paid_broker'] ? ($this->transactionBroker ? $this->transactionBroker->paid_by : '-') : "-";
    }
        public function getDeliveryPriceWithLabelAttribute()
    {
        $str = $this->delivery_price;
        $cur_str = NULL;
        if (!$this->delivery_price_cur || $this->delivery_price_cur == 1) $cur_str = 'USD';
        else $cur_str = $this->delivery_price_cur;
        if ($str && $cur_str) $str .= ' ' . $cur_str;
        return $str;
    }

    public function getShippingAmountKztAndUsdAttribute()
    {
        $currency = $this->delivery_price_cur ?? 'KZT';
        $original = $this->shipping_amount;
        $usd = convertToUSD($original, $currency);

        if ($this->partner_id == 3) {
            return number_format($original, 2) . ' ' . $currency . ' (' . number_format($usd, 2) . ' USD)';
        }

        return $original;
    }

    public function getDeliveryAmountKztAndUsdAttribute()
    {
        $currency = $this->delivery_price_cur ?? 'KZT';
        $original = $this->delivery_price;
        $usd = convertToUSD($original, $currency);

        if ($this->partner_id == 3) {
            return number_format($original, 2) . ' ' . $currency . ' (' . number_format($usd, 2) . ' USD)';
        }

        return $original;
    }

    public function getDeliveryAmountUsdAttribute()
    {
        if ($this->partner_id != 3) {
            return null;
        }

        $usd = convertToUSD($this->delivery_price, 'KZT');
        return number_format($usd, 2);
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

    public function getShippingAmountWithLabelAttribute()
    {
        $str = $this->shipping_amount;
        $cur_str = NULL;
        if ($this->currency == 1) $cur_str = 'USD';
        else $cur_str = $this->currency;
        if ($str && $cur_str) $str .= ' ' . $cur_str;
        return $str;
    }

    public function getTotalPriceUSDWithLabelAttribute()
    {
        $amount = 0;
        $cur_str = NULL;
        if ($this->delivery_price_cur == 1) $cur_str = 'USD';
        else $cur_str = $this->delivery_price_cur;
        if ($this->delivery_price)
            if ($cur_str == 'USD')
                $amount += $this->delivery_price;
            else
                $amount += convertToUSD($this->delivery_price, $cur_str);
        $cur_str = NULL;
        if ($this->currency == 1) $cur_str = 'USD';
        else $cur_str = $this->currency;
        if ($this->shipping_amount)
            if ($cur_str == 'USD')
                $amount += $this->shipping_amount;
            else
                $amount += convertToUSD($this->shipping_amount, $cur_str);
        if ($amount && $amount > 0) return $amount . ' USD';
        return '';
    }

    public function getCityIdWithLabelAttribute()
    {
        if (!$this->city) {
            return '';
        }
        return $this->city->name;
    }

    public function delivery_point()
    {
        return $this->belongsTo(DeliveryPoint::class, 'store_status');
    }

    public function azeriexpress_office()
    {
        return $this->belongsTo(AzeriExpress\AzeriExpressOffice::class, 'azeriexpress_office_id');
    }

    public function azerpost_office()
    {
        return $this->belongsTo('App\Models\Azerpost\AzerpostOffice', 'azerpost_office_id');
    }

    public function surat_office()
    {
        return $this->belongsTo('App\Models\Surat\SuratOffice', 'surat_office_id');
    }

    public function courierDelivery()
    {
        return $this->belongsTo('App\Models\CD', 'courier_delivery_id');
    }

    public function yenipoct_office()
    {
        return $this->belongsTo('App\Models\YeniPoct\YenipoctOffice', 'yenipoct_office_id');
    }

    public function kargomat_office()
    {
        return $this->belongsTo('App\Models\Kargomat\KargomatOffice', 'kargomat_office_id');
    }

    public function shelf()
    {
        return $this->belongsTo('App\Models\CourierShelf', 'shelf_id');
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

    public function getFilialPudoNameAttribute()
    {
        if ($this->delivery_type != 'PUDO') return NULL;
        if (!$this->filial) return NULL;
        return $this->filial->type_id_name;
    }

    public function filial()
    {
        return $this->belongsTo('App\Models\Filial', 'filial_type_id');
    }

    public function getFilialHdNameAttribute()
    {
        if ($this->delivery_type == 'PUDO') return NULL;
        if (!$this->filial) return NULL;
        return $this->filial->type_id_name;
    }

    public function getFilialHdNameWithLabelAttribute()
    {
        return $this->filial_hd_name;
    }

    public function getShelfNameAttribute()
    {
        if (!$this->shelf_id)
            return '-';
        $shelf = CourierShelf::find($this->shelf_id);
        if ($shelf)
            return $shelf->name;
        return '-';
    }

    public function getFilialTypeIdAttribute()
    {
        if ($this->store_status && $this->delivery_point) return 'ASE-' . $this->delivery_point->id;
        if ($this->azeriexpress_office_id && $this->azeriexpress_office) return 'AZEXP-' . $this->azeriexpress_office->id;
        if ($this->azerpost_office_id && $this->azerpost_office) return 'AZPOST-' . $this->azerpost_office->id;
        if ($this->surat_office_id && $this->surat_office) return 'SURAT-' . $this->surat_office->id;
        if ($this->yenipoct_office_id && $this->yenipoct_office) return 'YP-' . $this->yenipoct_office->id;
        if ($this->kargomat_office_id && $this->kargomat_office) return 'KG-' . $this->kargomat_office->id;
        if ($this->unknown_office_id && $this->unknown_office) return 'UNKNOWN-' . $this->unknown_office->id;
        return NULL;
    }

    public function getFilialTypeIdForMeestAttribute()
    {
        if ($this->store_status && $this->delivery_point) return 'ASE' . $this->delivery_point->id;
        if ($this->azeriexpress_office_id && $this->azeriexpress_office) return 'AZEXP' . $this->azeriexpress_office->id;
        if ($this->azerpost_office_id && $this->azerpost_office) return 'AZPOST' . $this->azerpost_office->id;
        if ($this->surat_office_id && $this->surat_office) return 'SURAT' . $this->surat_office->id;
        if ($this->yenipoct_office_id && $this->yenipoct_office) return 'YP' . $this->yenipoct_office->id;
        if ($this->kargomat_office_id && $this->kargomat_office) return 'KR' . $this->kargomat_office->id;
        if ($this->unknown_office_id && $this->unknown_office) return 'UNKNOWN-' . $this->unknown_office->id;
        return NULL;
    }

    public function getFilialDetailsAttribute()
    {
        if ($this->store_status && $this->delivery_point) return $this->delivery_point . ' _(ASE)';
        if ($this->azeri_express_office_id && $this->azeri_express_office) return $this->azeri_express_office . ' _(AZEXP)';
        if ($this->azerpost_office_id && $this->azerpost_office) return $this->azerpost_office . ' _(AZPOST)';
        if ($this->surat_office_id && $this->surat_office) return $this->surat_office . ' _(SURAT)';
        if ($this->yenipoct_office_id && $this->yenipoct_office) return $this->yenipoct_office . ' _(YP)';
        if ($this->kargomat_office_id && $this->kargomat_office) return $this->kargomat_office . ' _(KG)';
        if ($this->unknown_office_id && $this->unknown_office) return $this->unknown_office . ' _(UNKNOWN)';
        return NULL;
    }

    public function getFilialNameAttribute()
    {
        if ($this->store_status && $this->delivery_point) return $this->delivery_point->description . ' (ASE)';
        if ($this->azeriexpress_office_id && $this->azeriexpress_office) return $this->azeriexpress_office->description . ' (AZXP)';
        if ($this->azerpost_office_id && $this->azerpost_office) return $this->azerpost_office->name . ' (AZPOST)';
        if ($this->surat_office_id && $this->surat_office) return $this->surat_office->name . ' (SURAT)';
        if ($this->yenipoct_office_id && $this->yenipoct_office) return $this->yenipoct_office->name . ' (YP)';
        if ($this->kargomat_office_id && $this->kargomat_office) return $this->kargomat_office->name . ' (KARGOMAT)';
        return '';
    }


    public function unitradePackage()
    {
        return $this->belongsTo(UnitradePackage::class, 'id', 'track_id');
    }

    public function carrier()
    {
        return $this->belongsTo('App\Models\PackageCarrier', 'id', 'track_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function partner()
    {
        return $this->belongsTo('App\Models\Partner');
    }

    public function goods()
    {
        return $this->hasMany(PackageGood::class);
    }

    public function airbox()
    {
        return $this->belongsTo('App\Models\Airbox')->withTrashed();
    }

    public function getAirboxNameAttribute()
    {
        if ($this->airbox) return $this->airbox->name;
        return '';
    }

    public function container()
    {
        return $this->belongsTo('App\Models\Container')->withTrashed();
    }

    public function getContainerNameAttribute()
    {
        if ($this->container) return $this->container->name;
        return '';
    }

    public function city()
    {
        return $this->belongsTo('App\Models\City')->withTrashed();
    }

    public function courier_delivery()
    {
        return $this->belongsTo(CD::class);//->withTrashed();
    }

    public function warehouse()
    {
        return $this->belongsTo('App\Models\Warehouse')->withTrashed();
    }

    public function country()
    {
        return $this->belongsTo('App\Models\Country');//->withTrashed();
    }

    public function getCarrierIdAttribute()
    {
        if ($this->carrier) {
            return $this->carrier->id;
        }

        return NULL;
    }

    public function getPartnerWithLabelAttribute()
    {
        $partner_id = $this->attributes['partner_id'] ?? null;
        if (!$partner_id) {
            return '';
        }

        $partners = config('ase.attributes.track.partner');

        return $partners[$partner_id] ?? '';
    }

    public function getDeliveryPriceOzonWithLabelAttribute()
    {
        $amount = $this->delivery_price_ozon;
        if ($amount)
            return $amount . ' USD';
        return null;
    }


    public function getDeliveryPriceOzonAttribute()
    {
        if (!$this->weight) return null;
        if ($this->partner_id != 3) return null;
        if (in_array($this->status, [26, 27])) return null;
        if ($this->delivery_type == 'PUDO') {
            if ($this->weight <= 5) {
                return 1.2;
            }
            return round(1.2 + ($this->weight - 5) * 0.3, 2);
        }
        if ($this->weight <= 5) {
            return 2;
        }
        return round(2 + ($this->weight - 5) * 0.4, 2);
    }

    public function getDeliveryPriceGfsWithLabelAttribute()
    {
        $amount = $this->delivery_price_gfs;
        if ($amount)
            return $amount . ' USD';
        return null;
    }

    public function getDeliveryPriceGfsAttribute()
    {
        if (!$this->weight) return null;
        if ($this->partner_id != 8) return null;
        if ($this->weight <= 2) {
            return 0.9;
        }
        return round(0.9 + ($this->weight - 2) * 0.05, 2);
        /*$track_city=strtolower(a2l($this->city_name));
        if($track_city != 'baki') {
            if($this->weight <= 2) {
                return 2.8;
            }
            return round( 2.8 + ($this->weight - 2) * 0.5 , 2) ;
        }
        if($this->weight <= 2) {
            return 2;
        }
            return round( 2 + ($this->weight - 2) * 0.35 , 2);
         */
        /*$cd=$this->courier_delivery;
        if(!$cd) return null;
        $courier=$cd->courier;
        if(!$courier) return null;
        if($courier->name == 'Azeriexpress') {
            if($this->weight <= 2) {
                return 2.8;
            }
            return round( 2.8 + ($this->weight - 2) * 0.5 , 2) ;
        }
        if($this->weight <= 2) {
            return 2;
        }
        return round( 2 + ($this->weight - 2) * 0.35 , 2);*/
    }

    public static function auto_courier($track)
    {
        if (!$track) {
            return false;
        }
        if ($track->courier_delivery) {
            return false;
        }
        $courier = CA::areaCourier($track);
        if (!$courier) {
            return false;
        }
        $cd_status = 1; //accepted
        $cd = CD::newCD($track, $courier->id, $cd_status);
        if ($cd) {
            $cd->save();
            $track->courier_delivery_id = $cd->id;
            $track->bot_comment = 'Auto courier';
            return true;
        }
        return false;
    }

    public function getStatusWithLabelAttribute()
    {
        $status = $this->attributes['status'] ?? null;
        if (!$status) {
            return '';
        }

        $statusShort = config('ase.attributes.track.statusShort');
        $statusFull = config('ase.attributes.track.status');

        return $statusShort[$status] ?? $statusFull[$status] ?? 'Unknown (' . $status . ')';
    }

    public function parseCity()
    {
        if ($this->city_id)
            return;
        if (in_array($this->city_name, ['Bakı']))
            $this->city_id = 1;
        if (in_array(strtoupper($this->city_name), ['BAKU', 'BAKI', 'BAKI']))
            $this->city_id = 1;
        if (in_array(strtoupper($this->city_name), ['SUMQAYIT', 'SUMGAYIT', 'SUMQAYIT']))
            $this->city_id = 3;
        if (in_array($this->city_name, ['Sumqayıt']))
            $this->city_id = 3;
        if (in_array(strtoupper($this->city_name), ['GANJA', 'GƏNCƏ']))
            $this->city_id = 5;
    }

    public function assignCustomer()
    {
        /*$customer = $this->customer;
	if($customer && ($customer->partner_id != $this->partner_id))
	    $customer=NULL;
        $customer_changed = false;
        if (!$customer && $this->email) {
            $customer = Customer::where('email', $this->email)->whereNotNull('email')->where('partner_id', $this->partner_id)->first();
        }
        if (!$customer && $this->phone) {
            $customer = Customer::where('phone', $this->phone)->whereNotNull('phone')->where('partner_id', $this->partner_id)->first();
        }
        if (!$customer && $this->fullname) {
            $customer = Customer::where('fullname', $this->fullname)->whereNotNull('fullname')->where('partner_id', $this->partner_id)->first();
        }
        if (!$customer && $this->fin) {
            $customer = Customer::where('fin', $this->fin)->whereNotNull('fin')->where('partner_id', $this->partner_id)->first();
	}*/
        $customer_changed = false;
        $str = "(partner_id=" . $this->partner_id . ") and (fullname='" . $this->fullname . "')";
        if ($this->phone)
            $str .= " and (phone='" . $this->phone . "')";
        if ($this->fin)
            $str .= " and (fin='" . $this->fin . "')";
        $customer = Customer::whereRaw("(" . $str . ")")->first();
        if (!$customer) {
            $customer = new Customer();
            $customer->partner_id = $this->partner_id;
            $customer->fullname = $this->fullname;
            $customer->phone = $this->phone;
            $customer->fin = $this->fin;
            $customer->email = $this->email;
            $customer->address = $this->address;
            $customer->zip_code = $this->zip_code;
            $customer->city_id = $this->city_id;
            $customer->city_name = $this->city_name;
            $customer->region_name = $this->region_name;
            $customer_changed = true;
        }
        if (!empty($this->fullname) && ($this->fullname != $customer->fullname)) {
            $customer->fullname = $this->fullname;
            $customer_changed = true;
        }
        if (!empty($this->phone) && ($this->phone != $customer->phone)) {
            $customer->phone = $this->phone;
            $customer_changed = true;
        }
        if (!empty($this->email) && ($this->email != $customer->email)) {
            $customer->email = $this->email;
            $customer_changed = true;
        }
        if (!empty($this->address) && ($this->address != $customer->address)) {
            $customer->address = $this->address;
            $customer_changed = true;
        }
        if (!empty($this->zip_code) && ($this->zip_code != $customer->zip_code)) {
            $customer->zip_code = $this->zip_code;
            $customer_changed = true;
        }
        if (!empty($this->city_name) && ($this->city_name != $customer->city_name)) {
            $customer->city_name = $this->city_name;
            $customer_changed = true;
        }
        if (!empty($this->city_id) && ($this->city_id != $customer->city_id)) {
            $customer->city_id = $this->city_id;
            $customer_changed = true;
        }
        if (!empty($this->fin) && ($this->fin != $customer->fin)) {
            $customer->fin = $this->fin;
            $customer_changed = true;
        }
        if (!empty($this->region_name) && ($this->region_name != $customer->region_name)) {
            $customer->region_name = $this->region_name;
            $customer_changed = true;
        }
        if ($customer_changed) {
            $customer->save();
        }
        $this->customer_id = $customer->id;
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

    public function getTrackingAttribute()
    {
        return $this->tracking_code;
    }

    public function getUserIdAttribute()
    {
        return $this->customer_id;
    }

    public function getWeightGoodsAttribute($value)
    {
        if (!empty($value))
            return $value + 0;
    }

    public function getDeclaredWeightGoodsAttribute()
    {
        if ($this->carrier && ($this->carrier->status || $this->carrier->ecoM_REGNUMBER)) {
            return $this->weight + 0;
        } else {
            return 0;
        }
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

    public function azeriexpresspackage()
    {
        return $this->belongsTo(AzeriExpressPackage::class, 'id', 'package_id')->where('type', 'track');
    }

    public function precinctpackage()
    {
        return $this->belongsTo(PrecinctPackage::class, 'id', 'package_id')->where('type', 'track');
    }

    public function azerpostpackage()
    {
        return $this->belongsTo(AzerpostPackage::class, 'id', 'package_id')->where('type', 'track');
    }

    public function suratpackage()
    {
        return $this->belongsTo(SuratPackage::class, 'id', 'package_id')->where('type', 'track');
    }

    public function yenipoctpackage()
    {
        return $this->belongsTo(YenipoctPackage::class, 'id', 'package_id')->where('type', 'track');
    }

    public function kargomatpackage()
    {
        return $this->belongsTo(KargomatPackage::class, 'id', 'package_id')->where('type', 'track');
    }

    public function getCarrierstatusLabelAttribute()
    {
        if ($this->azeriexpresspackage) {
            return __('admin.azeriexpress_warehouse_package_status_' . $this->azeriexpresspackage->status) . ' (AzeriEpress)';
        }

        if ($this->precinctpackage) {
            return __('admin.precinct_warehouse_package_status_' . $this->precinctpackage->status) . ' (Precinct)';
        }

        if ($this->azerpostpackage) {
            return __('admin.azerpost_warehouse_package_status_' . $this->azerpostpackage->status) . ' (Azerpost)';
        }

        if ($this->suratpackage) {
            return __('admin.surat_warehouse_package_status_' . $this->suratpackage->status) . ' (Surat)';
        }

        if ($this->yenipoctpackage) {
            return __('admin.yenipoct_warehouse_package_status_' . $this->yenipoctpackage->status) . ' (YeniPoct)';
        }

        if ($this->kargomatpackage) {
            return __('admin.kargomat_warehouse_package_status_' . $this->kargomatpackage->status) . ' (Kargomat)';
        }

        return '-';
    }

    public function getCarrierstatusLabelTrackingAttribute()
    {
        if ($this->azeriexpresspackage) {
            return __('admin.azeriexpress_warehouse_package_status_' . $this->azeriexpresspackage->status);
        }

        if ($this->precinctpackage) {
            return __('admin.precinct_warehouse_package_status_' . $this->precinctpackage->status);
        }

        if ($this->azerpostpackage) {
            return __('admin.azerpost_warehouse_package_status_' . $this->azerpostpackage->status);
        }

        if ($this->suratpackage) {
            return __('admin.surat_warehouse_package_status_' . $this->suratpackage->status);
        }

        if ($this->yenipoctpackage) {
            return __('admin.yenipoct_warehouse_package_status_' . $this->yenipoctpackage->status);
        }

        if ($this->kargomatpackage) {
            return __('admin.kargomat_warehouse_package_status_' . $this->kargomatpackage->status);
        }
        return '-';
    }

    public function getAzeriexpressstatusLabelAttribute()
    {
        if (!$this->azeriexpresspackage) {
            return '-';
        }

        return __('admin.azeriexpress_warehouse_package_status_' . $this->azeriexpresspackage->status);
    }

    public function getPrecinctstatusLabelAttribute()
    {
        if (!$this->precinctpackage || $this->azeriexpresspackage || $this->azerpostpackage) {
            return '-';
        }

        return __('admin.precinct_warehouse_package_status_' . $this->precinctpackage->status);
    }

    public function getAzerpoststatusLabelAttribute()
    {
        if (!$this->azerpostpackage) {
            return '-';
        }

        return __('admin.azerpost_warehouse_package_status_' . $this->azerpostpackage->status);
    }

    public function getSuratstatusLabelAttribute()
    {
        if (!$this->suratpackage) {
            return '-';
        }

        return __('admin.surat_warehouse_package_status_' . $this->suratpackage->status);
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

    public static function generateCode()
    {
        $chars = '0123456789ABCDEFGHIJKLMNPQRSTUVWXYZ';
        do {
            $code = '';
            for ($x = 0; $x < 8; $x++) {
                $code .= $chars[rand(0, strlen($chars) - 1)];
            }

            $check = Track::where('custom_id', $code)->first();
            if (!$check) {
                break;
            }
        } while (true);

        return $code;
    }

    public function getCustomIdAttribute($value)
    {
        if (!$value) {
            $code = Track::generateCode();
            $this->custom_id = $code;
            $this->save();
            return $code;
        }
        return $value;
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

    public function carrierReset($deleteOnly = false)
    {
        if ($this->cm == null) {
            $this->cm = new CustomsModel(true);
            $this->cm->retryCount = 2;
            $this->cm->retrySleep = 0;
        }
        $this->cm->isCommercial = 0;
        $this->cm->trackingNumber = $this->tracking_code;
        $this->load('customer');
        $this->cm->fin = $this->customer->fin ?? $this->fin;
        $cpost = $this->cm->get_carrierposts2();
        if ($cpost->code != 200) {
            return;
        }
        if (!empty($cpost->inserT_DATE)) {
            if ($cpost->status) {
                return;
            }
            $res = $this->cm->delete_carriers();
            if (!isset($res->code) || ($res->code != 200) && ($res->code != 400))
                return;
        }
        DB::delete("delete from package_carriers where track_id=?", [$this->id]);
        if (!$deleteOnly) {
            $this->carrierAdd();
        }
    }

    public function carrierAdd()
    {
        $items = DB::select("select * from customs_countries");
        $cm_countries = [];
        foreach ($items as $item) {
            $cm_countries[strtolower($item->CODE_C)] = $item->CODE_N;
        }
        if ($this->cm == null) {
            $this->cm = new CustomsModel(true);
            $this->cm->retryCount = 2;
            $this->cm->retrySleep = 0;
        }
        $this->cm->isCommercial = 0;
        $this->cm->trackingNumber = $this->tracking_code;
        $this->cm->fin = $this->fin;
        if ((!$this->cm->fin || $this->cm->fin == '-') && $this->customer)
            $this->cm->fin = $this->customer->fin;
        $cm = $this->cm;
        $track = $this;

        $cm->phone = $track->phone;
        if (!$cm->phone)
            $cm->phone = $track->customer->phone;
        $fullName = $track->fullname;
        if (!$fullName)
            $fullName = $track->customer->fullname;
        $address = $track->address;
        $countryCode = '';
        if ($track->partner_id == 1)
            $countryCode = 'us';
        if ($track->partner_id == 2)
            $countryCode = 'ru';
        if ($track->partner_id == 3)
            $countryCode = 'ru';
        if ($track->partner_id == 8)
            $countryCode = 'cn';
        if ($track->partner_id == 9)
            $countryCode = 'cn';
        if (empty($cm->fin) || empty(trim($address)) || !array_key_exists($countryCode, $cm_countries) || !$track->weight || !$track->number_items/*|| ($package->u_is_commercial && empty($package->u_voen))*/) {
            if (empty($cm->fin)) {
                $this->errorStr = 'Empty fin';
                return false;
            }
            if (!$track->weight) {
                $this->errorStr = 'Empty weight';
                return false;
            }
            if (!$track->number_items) {
                $this->errorStr = 'Empty number_items<';
                return false;
            }
            if (empty(trim($address))) {
                $this->errorStr = 'Empty address<';
                return false;
            }
            if (!array_key_exists($countryCode, $cm_countries)) {
                $this->errorStr = "Wrong country code: " . $countryCode;
                return false;
            }
        }
        $shippingAmount = $track->shipping_amount;
        if (!$shippingAmount)
            $shippingAmount = 0;
        $deliveryAmount = $track->delivery_price;
        if (!$deliveryAmount)
            $deliveryAmount = 0.01;

        $webSiteName = getOnlyDomainWithExt($track->website);
        if (empty($webSiteName) || $webSiteName == '-') {
            $webSiteName = $track->partner->website;
        }
        if (empty($webSiteName) || $webSiteName == '-') {
            if ($track->partner_id == 1)
                $webSiteName = 'iherb.com';
            if ($track->partner_id == 2)
                $webSiteName = 'wildberries.ru';
            if ($track->partner_id == 3)
                $webSiteName = 'ozon.ru';
        }

        $addressStr = $track->partner->address;
        //$cm->goods_idList = [0];
        //$cm->name_of_goodsList = ['-'];
        $cm->get_goods_noid($track->goods);

        $whtsp = array("\r\n", "\n", "\r");
        $cm->direction = 1;
        $cm->trackinG_NO = $cm->trackingNumber;
        if ($track->delivery_price_cur)
            $cm->transP_COSTS = convertToUSD($deliveryAmount, $track->delivery_price_cur);
        else
            $cm->transP_COSTS = $deliveryAmount;
        $cm->weighT_GOODS = $track->weight;
        if (!$cm->weighT_GOODS)
            $cm->weighT_GOODS = rand(10, 100) / 100;
        $cm->quantitY_OF_GOODS = $track->number_items;
        if (!$cm->quantitY_OF_GOODS)
            $cm->quantitY_OF_GOODS = 0;
        $cm->invoyS_PRICE = $shippingAmount;
        if ($track->currency && in_array($track->currency, array_values(config('ase.attributes.customsCurrencies')))) {
            foreach (config('ase.attributes.customsCurrencies') as $c_key => $c_value) {
                if ($c_value == $track->currency) {
                    if ($track->partner_id == 3) {
                        $cm->currencY_TYPE = 840;
                        break;
                    }else{
                        $cm->currencY_TYPE = $c_key;
                        break;
                    }
                }
            }
        } else {
            $cm->currencY_TYPE = "840";
        }
        if ($track->partner_id == 3) {
            $currency = $track->delivery_price_cur ?? 'KZT';
            $cm->invoyS_PRICE = convertToUSD($shippingAmount, $currency);
            $cm->currencY_TYPE = 840;
        }

        $cm->document_type = "PinCode";
        if (strlen($cm->fin) == 9 /*&& strtoupper(substr($cm->fin, 0, 1)) == 'P'*/)
            $cm->document_type = "PassportNumber";
        $cm->idxaL_NAME = str_replace('"', '\"', $fullName);
        $cm->idxaL_ADRESS = $address;
        $cm->idxaL_ADRESS = str_replace("\\", "\\\\", $cm->idxaL_ADRESS);
        $cm->idxaL_ADRESS = str_replace('"', '\"', $cm->idxaL_ADRESS);
        $cm->idxaL_ADRESS = str_replace($whtsp, ' ', $cm->idxaL_ADRESS);
        $cm->phone = str_replace("\\", "\\\\", $cm->phone);
        $cm->ixraC_NAME = str_replace('"', '\"', $webSiteName);
        $cm->ixraC_ADRESS = str_replace('"', '\"', $addressStr);
        $cm->goodS_TRAFFIC_FR = $cm_countries[$countryCode];
        $cm->goodS_TRAFFIC_TO = "031";
        $res = $cm->add_carriers();

        $ldate = date('Y-m-d H:i:s');

        if (!isset($res->code)) {
            sleep(1);
            $res = $cm->add_carriers();
            $ldate = date('Y-m-d H:i:s');
        }
        if (!isset($res->code)) {
            $this->errorStr = 'Empty reposnose';
            //echo $cm->get_carriers_json_str()."\n";
            $cm->updateTrackDB2($track->id, $cm->fin, $cm->trackingNumber, $ldate, 999);
            if ($track->status != 6 and $track->status!=18) {
                $track->status = 6;
                $track->save();
                (new PackageService())->updateStatus($track, 6);
            }
            return false;
        }
        if (($res->code == 400) && isset($res->exception) && is_object($res->exception) && isset($res->exception->status) && $res->exception->status == 'error')
            $res->code = 888;
        $cm->updateTrackDB2($track->id, $cm->fin, $cm->trackingNumber, $ldate, $res->code, NULL, $cm->idxaL_NAME, $cm->ixraC_NAME);
        if ($res->code == 200) {
            $track->bot_comment = "Track has been added to Customs again";
            if ($track->status != 5 and $track->status!=18) {
                $track->status = 5;
                $track->save();
                Notification::sendTrack($track->id, 5);
                (new PackageService())->updateStatus($track, 5);
            }else{
                $track->save();
            }
            return true;
        }
        if (isset($res->exception) && is_object($res->exception)) {
            $exception = $res->exception;
            $errorMessage = $exception->errorMessage;
            $errs = [];
            if (is_array($exception->validationError))
                $errs = $exception->validationError;
            if (is_object($exception->validationError))
                $errs = get_object_vars($exception->validationError);
            $validationError = '';
            foreach ($errs as $x => $x_value) {
                if (!empty($validationError))
                    $validationError .= " , ";
                $validationError .= $x . "=>" . $x_value;
            }
            $this->errorStr = "errorMessage: " . $errorMessage . " " . "validationError: " . $validationError;
            //$validationError=json_encode($exception->validationError);
        }
        if ($track->status != 6) {
            $track->status = 6;
            $track->save();
            (new PackageService())->updateStatus($track, 6);
        }
        return false;
    }

    public function getHdFilialAttribute()
    {
        if ($this->delivery_type == "HD") {
            if ($this->azeriexpress_office_id != null) {
                return "EX-" . $this->azeriexpress_office_id;
            }

            if ($this->azerpost_office_id != null) {
                return "AZ-" . $this->azerpost_office_id;
            }

            if ($this->surat_office_id != null) {
                return "SR-" . $this->surat_office_id;
            }

            if ($this->yenipoct_office_id != null) {
                return "YP-" . $this->yenipoct_office_id;
            }

            if ($this->kargomat_office_id != null) {
                return "KG-" . $this->kargomat_office_id;
            }

            if ($this->store_status != null) {
                return "DP-" . $this->store_status;
            }
        }
        return "";
    }

    public function setComment($value)
    {
        return $this->comment_txt = $value;
    }
}
