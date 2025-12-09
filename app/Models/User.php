<?php

namespace App\Models;

use App\Models\Extra\SMS;
use App\Notifications\ResetPassword;
use App\Traits\ModelEventLogger;
use App\Traits\Password;
use Cache;
use Carbon\Carbon;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Lunaweb\EmailVerification\Contracts\CanVerifyEmail as CanVerifyEmailContract;
use Lunaweb\EmailVerification\Traits\CanVerifyEmail;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property string $password
 * @property string|null $phone
 * @property string $passport
 * @property string $customer_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $company
 * @property string $passporta
 * @property string $passportb
 * @property string $agreement
 * @property string|null $remember_token
 * @property Carbon|null $login_at
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|User onlyTrashed()
 * @method static bool|null restore()
 * @method static Builder|User whereAddress($value)
 * @method static Builder|User whereCity($value)
 * @method static Builder|User whereCompany($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCustomerId($value)
 * @method static Builder|User whereDeletedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereLoginAt($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassport($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereSurname($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|User withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|Package[] $packages
 * @property string|null $zip_code
 * @property-read mixed $full_name
 * @method static Builder|User whereZipCode($value)
 * @property string|null $birthday
 * @property int $gender
 * @method static Builder|User whereBirthday($value)
 * @method static Builder|User whereGender($value)
 * @property string|null $old_password
 * @method static Builder|User whereOldPassword($value)
 * @property string|null $friend_reference
 * @property string|null $pass_key
 * @method static Builder|User whereFriendReference($value)
 * @method static Builder|User wherePassKey($value)
 * @property string|null $fin
 * @method static Builder|User whereFin($value)
 * @property string|null $sms_verification_code
 * @property int $sms_verification_status
 * @property bool $verified
 * @method static Builder|User whereSmsVerificationCode($value)
 * @method static Builder|User whereSmsVerificationStatus($value)
 * @method static Builder|User whereVerified($value)
 * @property int|null $city_id
 * @property int $check_verify
 * @property-read mixed $city_name
 * @property-read mixed $cleared_phone
 * @property-read mixed $pos_passport
 * @property-read mixed $pre_passport
 * @property-read mixed $rate
 * @property-read int|null $notifications_count
 * @property-read int|null $packages_count
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User whereCheckVerify($value)
 * @method static Builder|User whereCityId($value)
 * @property int|null $parent_id
 * @property string $status
 * @property-read Collection|User[] $children
 * @property-read int|null $children_count
 * @property-read User|null $dealer
 * @property-read mixed $is_banned
 * @property-read mixed $order_balance
 * @property-read mixed $package_balance
 * @property-read mixed $referral_balance
 * @property-read mixed $referrer_balance
 * @property-read Collection|Transaction[] $referralTransactions
 * @property-read int|null $referral_transactions_count
 * @property-read Collection|User[] $referrals
 * @property-read int|null $referrals_count
 * @property-read User $referrer
 * @property-read Collection|Transaction[] $transactions
 * @property-read int|null $transactions_count
 * @method static Builder|User whereParentId($value)
 * @method static Builder|User whereStatus($value)
 */
class User extends Authenticatable implements CanVerifyEmailContract
{
    use Notifiable;
    use SoftDeletes;
    use Password;
    use CanVerifyEmail;
    use ModelEventLogger;

    public $uploadDir = 'uploads/passport/';

    protected $appends = ['full_name'];

    /**
     * @var array
     */
    protected $dates = ['login_at', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'surname',
        'phone',
        'passport',
        'fin',
        'company',
        'email',
        'password',
        'customer_id',
        'city_id',
        'address',
        'sms_verification_code',
        'sms_verification_status',
        'promo_id',
        'azerpoct_send',
        'zip_code',
	'azeri_express_use',
	'azeri_express_office_id',
        'yenipoct_use',
        'yenipoct_office_id',
        'kargomat_use',
        'kargomat_office_id',
	'surat_use',
	'surat_office_id',
	'store_status',
	'warning_num',
        'sms_verification_code_queried_at',
	'otp_code',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $with = ['city'];

    public function dealer()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function getIds()
    {
	$arr=[$this->id];
	foreach($this->children as $c)
	    $arr[]=$c->id;
	return $arr;
    }

    /**
     * @return HasMany
     */
    public function packages()
    {
        return $this->hasMany('App\Models\Package');
    }


    public function getStoreStatusLabelAttribute($value) {
	if(!$this->delivery_point) return '';
	return $this->delivery_point->name;
    }

    public function getPackagesPaidInbaku($cd_id = 0)
    {
        $users = $this->children()->pluck('id')->all();
        $user_id = $this->id;
        //$packages=Package::with(['courier_delivery'])->where('user_id',$user_id)->where('status','=',2)->where('paid','>=',1)->get();
        $packages = Package::with(['courier_delivery'])->where(function ($query) use ($user_id, $users) {
            $query->where('user_id', $user_id)->orWhereIn('user_id', $users);
        })->whereIn('status', [2,8])->where('paid', '>=', 1)->get();
        $out = [];

//        dd($packages->map(function ($item) use ($out) {
//            return [
//                "azeri_express_office_id"=>$item->azeri_express_office_id,
//                "azeri_express_office_id"=>$item->azeri_express_office_id,
//                "azerpost_office_id"=>$item->azerpost_office_id,
//                "surat_office_id"=>$item->surat_office_id,
//
//                "yenipoct_office_id"=>$item->yenipoct_office_id,
//                "kargomat_office_id"=>$item->kargomat_office_id,
//                "store_status"=>$item->store_status,
//                'courier_delivery'=>$item->courier_delivery,
//            ];
//        }));
        foreach ($packages as $package) {
           if($package->azeri_express_office_id)
	                continue;
           if($package->azerpost_office_id)
	                continue;
           if($package->surat_office_id)
	                continue;
            if($package->yenipoct_office_id)
                continue;
            if($package->kargomat_office_id)
                continue;
           if($package->store_status && ($package->store_status!=1 && $package->store_status!=2))
	                continue;
            $cd = $package->courier_delivery;
            if ((!$cd || $cd->deleted_at) || ($cd_id && ($cd && $cd_id == $cd->id))) {
                $out[$package->id] = $package->tracking_code . " " . $package->custom_id . " " . $package->detailed_type;
                $str = $package->tracking_code . " " . $package->custom_id;
                if ($package->user_id != $user_id)
                    $str .= " (" . $package->user->customer_id . ")";
                $str .= " " . $package->detailed_type;
                $out[$package->id] = $str;
            }
        }
        return $out;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function referralTransactions()
    {
        return $this->hasMany(Transaction::class, 'referral_id');
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by');
    }

    /**
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public static function generateCode()
    {
        $prefix = env('MEMBER_PREFIX_CODE', 'ASE');
        $chars = '0123456789';
        /*$latest = (User::orderBy('customer_id', 'DESC')->first())->customer_id;
        $latest = intval(str_replace($prefix, "", $latest));*/
        do {
            $latest = '';
            for ($x = 0; $x < 5; $x++) {
                $latest .= $chars[rand(0, strlen($chars) - 1)];
            }

            //$latest++;

            $code = $prefix . substr((string)$latest, 0, 5);
            $check = User::withTrashed()->whereCustomerId($code)->first();
            if (!$check) {
                break;
            }
        } while (true);

        return $code;
    }

    public function getCommercialAttribute()
    {
        if ($this->is_commercial)
            return 'YES';
        return 'NO';
    }

    public function getPassportaAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : '';
    }

    public function getPassportbAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : '';
    }

    public function getAgreementAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : '';
    }

    public function getPassporta2Attribute($value)
    {
        if ($this->passporta) {
            //$imgSrc= asset($this->uploadDir . $this->passporta);
            $html = '<a target="_blank" style="text-decoration: none;" href="' . $this->passporta . '"><font color="blue">Yes</font></a>';
            echo $html;
            return;
        }
        return 'No';
    }

    public function getPassportb2Attribute($value)
    {
        if ($this->passportb) {
            $html = '<a target="_blank" style="text-decoration: none;" href="' . $this->passportb . '"><font color="blue">Yes</font></a>';
            echo $html;
            return;
        }
        return 'No';
    }

    public function getAgreement2Attribute($value)
    {
        if ($this->agreement) {
            $html = '<a target="_blank" style="text-decoration: none;" href="' . $this->agreement . '"><font color="blue">Yes</font></a>';
            echo $html;
            return;
        }
        return 'No';
    }

    public function getCustomerIdAttribute($value)
    {
        return str_replace("-", "", $value);
    }

    public function getRealStoreStatusAttribute($value)
    {
	if($this->real_azeri_express_use)
	    return 0;
        if($this->real_yenipoct_use)
            return 0;
        if($this->real_kargomat_use)
            return 0;
	if($this->real_surat_use)
	    return 0;
	if($this->real_azerpoct_send)
	    return 0;
	if($this->dealer) return $this->dealer->store_status;
	return $this->store_status;
    }

    public function getRealAzerpoctSendAttribute($value)
    {
	if($this->dealer) return $this->dealer->azerpoct_send;
	return $this->azerpoct_send;
    }

    public function getRealZipCodeAttribute($value)
    {
	if($this->dealer) return $this->dealer->zip_code;
	return $this->zip_code;
    }

    public function azerpost_office()
    {
	return $this->belongsTo('App\Models\Azerpost\AzerpostOffice', 'real_zip_code', 'name');
    }

    public function delivery_point()
    {
        return $this->belongsTo(DeliveryPoint::class, 'real_store_status');
    }

    public function getRealAzeriExpressUseAttribute($value)
    {
	if($this->dealer) return $this->dealer->azeri_express_use;
	return $this->azeri_express_use;
    }

    public function getRealSuratUseAttribute($value)
    {
	if($this->dealer) return $this->dealer->surat_use;
	return $this->surat_use;
    }

    public function getRealYenipoctUseAttribute($value)
    {
        if($this->dealer) return $this->dealer->yenipoct_use;
        return $this->yenipoct_use;
    }

    public function getRealKargomatUseAttribute($value)
    {
        if($this->dealer) return $this->dealer->kargomat_use;
        return $this->kargomat_use;
    }

    public function getRealCityIdAttribute($value)
    {
	if($this->dealer) return $this->dealer->city_id;
	return $this->city_id;
    }

    public function getRealAzeriExpressOfficeIdAttribute($value)
    {
	if(!$this->real_azeri_express_use)
	    return 0;
	if($this->dealer) return $this->dealer->azeri_express_office_id;
	return $this->azeri_express_office_id;
    }

    public function getRealSuratOfficeIdAttribute($value)
    {
	if(!$this->real_surat_use)
	    return 0;
	if($this->dealer) return $this->dealer->surat_office_id;
	return $this->surat_office_id;
    }

    public function getRealYenipoctOfficeIdAttribute($value)
    {
        if(!$this->real_yenipoct_use)
            return 0;
        if($this->dealer) return $this->dealer->yenipoct_office_id;
        return $this->yenipoct_office_id;
    }

    public function getRealKargomatOfficeIdAttribute($value)
    {
        if(!$this->real_kargomat_use)
            return 0;
        if($this->dealer) return $this->dealer->kargomat_office_id;
        return $this->kargomat_office_id;
    }

    public function azeri_express_office()
    {
        return $this->belongsTo(AzeriExpress\AzeriExpressOffice::class, 'real_azeri_express_office_id');
    }

    public function surat_office()
    {
        return $this->belongsTo(Surat\SuratOffice::class, 'real_surat_office_id');
    }

    public function yenipoct_office()
    {
        return $this->belongsTo(YeniPoct\YenipoctOffice::class, 'real_yenipoct_office_id');
    }

    public function kargomat_office()
    {
        return $this->belongsTo(Kargomat\KargomatOffice::class, 'real_kargomat_office_id');
    }

    public function real_city()
    {
        return $this->belongsTo(City::class, 'real_city_id');
    }

    public function getRealCityNameAttribute()
    {
	if(!$this->real_city_id)
            return "Bakı";
	$city=$this->real_city();
	if($city && $city->first())
	   return $city->first()->name;
        return "Bakı";
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function getCityNameAttribute()
    {
	if(!$this->city_id)
            return "Bakı";
	$city=$this->city();
	if($city && $city->first())
	   return $city->first()->name;
        return "Bakı";
/*        try {
            return Cache::remember('user_city_' . $this->id, 30 * 24 * 60, function () {
                return isset($this->attributes['city_id']) ? ($this->city()->first() ? $this->city()->first()->name : 'Bakı') : 'Bakı';
            });
        } catch (Exception $exception) {
            Artisan::call('cache:clear');
            return "Bakı";
	}*/

    }

    public function getRateAttribute()
    {
        $rateIndex = [
            'phone' => 1,
            'passport' => 1,
            'fin' => 1,
            'address' => 1,
            'city_id' => 1,
            'company' => 1,
            'zip_code' => 1,
            'birthday' => 1,
            'gender' => 1,
            'verified' => 1,
            'sms_verification_status' => 1,
        ];

        $totalRate = array_sum($rateIndex);

        $rate = 0;
        foreach ($rateIndex as $field => $index) {
            if (!empty($this->attributes[$field])) {
                $rate += $index;
            }
        }

        return round(100 * $rate / $totalRate, 2);
    }

    public function getEmailAttribute($value)
    {
        return strtolower($value);
    }

    public function getPrePassportAttribute()
    {
        $num = preg_replace('/[^0-9]/', '', $this->attributes['passport']);

        $rt = str_replace($num, "", $this->attributes['passport']);
        $rt = str_replace("-", "", $rt);

        return $rt;
    }

    public function getPosPassportAttribute()
    {
        return preg_replace('/[^0-9]/', '', $this->attributes['passport']);
    }

    public function getClearedPhoneAttribute()
    {
        return SMS::clearNumber($this->attributes['phone'], true);
    }

    public function getPassportAttribute($value)
    {
        return strtoupper($value);
    }

    public function getFullNameAttribute()
    {
        return ucfirst(Str::slug($this->attributes['name'], '_')) . ' ' . ucfirst(Str::slug($this->attributes['surname'], '_'));
    }

    public function getFinAttribute($value)
    {
        return $value ? strtoupper($value) : null;
    }

    public function getPackageBalanceAttribute()
    {
        $in = $this->transactions()->where('paid_for', 'PACKAGE_BALANCE')->where('type', 'IN')->sum('amount');
        $out = $this->transactions()->whereIn('paid_for', ['PACKAGE_BALANCE', 'PACKAGE'])->whereIn('type', [
            'DEBT',
            'OUT',
        ])->sum('amount');

        return ($in - $out) . ' ₼';
    }

    public function getReferralBalanceAttribute()
    {
        return $this->transactions()->where('paid_by', 'REFERRAL')->sum('amount') . ' ₼';
    }

    public function getOrderBalanceAttribute()
    {
        return '0 ₼';
    }

    public function getAzerpoctStatusAttribute()
    {
        if ($this->azerpoct_send) return '@AZERPOCT';
        return '';
    }

    public function getReferrerBalanceAttribute()
    {
        $total = $this->referralTransactions()->sum('amount');

        return $total . ' ₼';
    }

    public function getIsBannedAttribute()
    {
        return $this->attributes['status'] == 'BANNED';
    }

    protected static function boot()
    {
        parent::boot();
        static::updating(function ($query) {
            Cache::forget('user_city_' . $query->id);
        });
    }
}
