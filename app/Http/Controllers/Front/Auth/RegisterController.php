<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Extra\SMS;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Surat\SuratOffice;
use App\Models\City;
use App\Models\DeliveryPoint;
use App\Models\Promo;
use App\Models\User;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\YeniPoct\YenipoctOffice;
use Auth;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Lunaweb\EmailVerification\EmailVerification;
use Lunaweb\EmailVerification\Traits\VerifiesEmail;
use Session;
use Validator;
use Illuminate\Auth\Events\Registered;


//use Illuminate\Support\Facades\Request;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers, VerifiesEmail;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
//    protected $redirectTo = '/register/verify/resend';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', [
            'except' => [
                'verify',
                'showResendVerificationEmailForm',
                'resendVerificationEmail',
            ],
        ]);
        $this->middleware('auth', ['only' => ['showResendVerificationEmailForm', 'resendVerificationEmail']]);

        if (!env('EMAIL_VERIFY')) {
            $this->redirectTo = '/user';
        }
    }

    public function showResendVerificationEmailForm()
    {
        $user = Auth::user();

        if (!session('verification_email_sent') && !$user->verified) {
            resolve('Lunaweb\EmailVerification\EmailVerification')->sendVerifyLink($user);
            session(['verification_email_sent' => 'yes']);
        }

        return view('emailverification::resend', ['verified' => $user->verified, 'email' => $user->email]);
    }

    /**
     * Resend the verification mail
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
        ]);
        $user->email = $request->get('email');
        $user->save();

        $sent = resolve('Lunaweb\EmailVerification\EmailVerification')->sendVerifyLink($user);
        Session::flash($sent == EmailVerification::VERIFY_LINK_SENT ? 'success' : 'error', trans($sent));

        if ($sent == EmailVerification::VERIFY_LINK_SENT) {
            session(['verification_email_sent' => 'yes']);
        }

        return redirect($this->redirectPath());
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $data['passport'] = $data['passport_prefix'] . '-' . $data['passport_number'];
        $digits = 'digits:' . ($data['passport_prefix'] == 'AZE' ? 8 : 7);
//        $messages = [
//            'g-recaptcha-response.required' => 'You must check the reCAPTCHA.',
//            'g-recaptcha-response.captcha' => 'Captcha error! try again later or contact site admin.',
//        ];


        $validator = Validator::make($data, [
            'name' => 'required|string|max:30|regex:/(^([a-zA-Z]+)?$)/u',
            'surname' => 'required|string|max:30|regex:/(^([a-zA-Z]+)?$)/u',
            'phone' => 'required|string|unique:users',
            'passport_prefix' => 'required|in:AZE,AA',
            'passport_number' => 'required|' . $digits,
            'passport' => 'required|string|unique:users',
            'fin' => 'required|alpha_num|unique:users',
            'email' => 'required|email|string|max:255|unique:users',
            'password' => 'required|string|min:6',
            'address' => 'required|string|min:10',
            //'city' => 'required|integer|not_in:0',
            'zip_code' => 'nullable|string|not_in:0|min:4|required_if:azerpoct_send,==,on',
            'azerpoct_send' => 'nullable|string',
            //'azeri_express_use' => 'nullable|string',
            'azeri_express_office_id' => 'nullable|string',
            //'azeri_express_office_id' => 'required',
            //'promo' => 'nullable|string|min:4|max:8',
            'g-recaptcha-response' => 'required|captcha',
//            'promo' => ['nullable', Rule::exists('promos', 'code')->where(function ($query) {
//                $ldate = date('Y-m-d H:i:s');
//                $query->where('is_active', 1)->where('activation', 1)->whereRaw('num_to_use > num_used')
//                    ->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))");
//            }),
//            ],
        ]);

        //if ($validator->fails()) {
        //return response()->json($validator->messages(), Response::HTTP_BAD_REQUEST);
        //}
        return $validator;
    }


    public function send()
    {

        $user = Auth::user();

        if ($user->sms_verification_status) {
            return redirect('/');
        }

        $data = [
            'code' => $user->sms_verification_code,
            'user' => $user->name
        ];
        if (SMS::verifyNumber($user->phone, $data)) {
            return Session::flash('success', 'alindi');
        } else {
            return Session::flash('error', 'olmadi');
        }
    }

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        $user = Auth::user();

        $num = $user->phone;

        $user->phone = $num;
        $user->sms_verification_code = rand(1000, 9999);
        $user->save();

        $this->send();

        return redirect('/number/verify/code');
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     *
     * @return User
     */
    protected function create(array $data)
    {
        $promo_id = null;
        $ldate = date('Y-m-d H:i:s');

        if (isset($data['promo'])){
            $promo = Promo::where('code', $data['promo'])->where('is_active', 1)->where('activation', 1)->whereRaw('num_to_use > num_used')
                ->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))")->first();
        }

        if (isset($promo)) {
            $promo_id = $promo->id;
        }
        $azerpoct_send = 0;

        $azeri_express_use = 0;
        $surat_use = 0;
        $yenipoct_use = 0;
        $kargomat_use = 0;
        $store_status = 1;
        $azeri_express_office_id = NULL;
        $surat_office_id = NULL;
        $yenipoct_office_id = NULL;
        $kargomat_office_id = NULL;
        $zip_code = '';
        if ((array_key_exists('azeri_express_office_id', $data) && !empty($data['azeri_express_office_id'])) || isset($data['kargomat_id'])) {


            if(isset($data['azeri_express_office_id'])){
                $azeri_express_office_id = $data['azeri_express_office_id'];
            }else{
                $azeri_express_office_id = $data['kargomat_id'];
            }
            if (substr($azeri_express_office_id, 0, 4) == 'ase_') {
                $store_status = substr($azeri_express_office_id, 4);
                $azeri_express_use = 0;
                $surat_use = 0;
                $yenipoct_use = 0;
                $kargomat_use = 0;
                $azerpoct_send = 0;
                $azeri_express_office_id = NULL;
                $yenipoct_office_id = NULL;
                $kargomat_office_id = NULL;
            } else if (substr($azeri_express_office_id, 0, 4) == 'zip_') {
                $zip_code = substr($azeri_express_office_id, 4);
                $azeri_express_use = 0;
                $surat_use = 0;
                $yenipoct_use = 0;
                $kargomat_use = 0;
                $azerpoct_send = 1;
                $azeri_express_office_id = NULL;
                $yenipoct_office_id = NULL;
                $kargomat_office_id = NULL;
                $store_status = 1;
            } else if (substr($azeri_express_office_id, 0, 6) == 'surat_') {
                $surat_office_id = substr($azeri_express_office_id, 6);
                $azeri_express_use = 0;
                $surat_use = 1;
                $azerpoct_send = 0;
                $yenipoct_use = 0;
                $kargomat_use = 0;
                $azeri_express_office_id = NULL;
                $yenipoct_office_id = NULL;
                $kargomat_office_id = NULL;
                $store_status = 1;
            }else if (substr($azeri_express_office_id, 0, 3) == 'yp_') {
                $yenipoct_office_id = substr($azeri_express_office_id, 3);
                $azeri_express_use = 0;
                $surat_use = 0;
                $azerpoct_send = 0;
                $yenipoct_use = 1;
                $kargomat_use = 0;
                $azeri_express_office_id = NULL;
                $yenipoct_office_id = NULL;
                $surat_office_id = NULL;
                $kargomat_office_id = NULL;
                $store_status = 1;
            }else if (substr($azeri_express_office_id, 0, 9) == 'kargomat_') {
                $kargomat_office_id = substr($azeri_express_office_id, 9);
                $azeri_express_use = 0;
                $surat_use = 0;
                $azerpoct_send = 0;
                $yenipoct_use = 1;
                $kargomat_use = 1;
                $azeri_express_office_id = NULL;
                $surat_office_id = NULL;
                $store_status = 1;
            }else {
                $store_status = 1;
                $azerpoct_send = 0;
                $azeri_express_use = 1;
                $surat_use = 0;
                $yenipoct_office_id = NULL;
                $kargomat_office_id = NULL;
                $azeri_express_office_id = $azeri_express_office_id;
            }
        } else {
            $store_status = 1;
            $azerpoct_send = 0;
            $azeri_express_use = 0;
            $surat_use = 0;
            $azeri_express_office_id = NULL;
            $yenipoct_office_id = NULL;
            $kargomat_office_id = NULL;
        }
        $user = User::create([
            'name' => $data['name'],
            'surname' => $data['surname'],
            'email' => $data['email'],
            'password' => $data['password'],
            'address' => $data['address'],
            'passport' => $data['passport_prefix'] . '-' . $data['passport_number'],
            'fin' => $data['fin'],
            'phone' => $data['phone'],
            'customer_id' => User::generateCode(),
            'city_id' => $data['city'] ?? 1,
            'azerpoct_send' => $azerpoct_send,
            'promo_id' => $promo_id,
            'zip_code' => $zip_code,
            'azeri_express_use' => $azeri_express_use,
            'azeri_express_office_id' => $azeri_express_office_id,
            'surat_use' => $surat_use,
            'yenipoct_use' => $yenipoct_use,
            'kargomat_use' => $kargomat_use,
            'surat_office_id' => $surat_office_id,
            'yenipoct_office_id' => $yenipoct_office_id,
            'kargomat_office_id' => $kargomat_office_id,
            'store_status' => $store_status,
            'warning_number' => 1,
        ]);
        if (isset($promo)) {
            $promo->num_used++;
            $promo->save();
        }

        session(['verification_email_sent' => 'yes']);

        $user = User::find($user->id);

        /* Send notification */
        $message = null;
        $message .= "✅ <b>" . $user->full_name . "</b> (" . $user->customer_id . ") ";
        $message .= ($user->city_name ? ($user->city_name . " şəhərindən ") : null) . "qeydiyyatdan keçdi.";
        if (isset($promo)) {
            $message .= " promo code:" . $promo->code;
        }

        sendTGMessage($message);

        return $user;
    }

    public function showRegistrationForm()
    {
        $title = trans('front.menu.sign_up');
        $hideSideBar = $hideNavBar = true;
        $bodyClass = 'login-container login-cover  pace-done';
        $citiesObj = City::select('cities.*')->join('city_translations', 'cities.id', '=', 'city_translations.city_id')->orderBy('city_translations.name')->where('city_translations.locale', 'az')->get();
        $cities = [];
        $zipcities = [];

        $zipcodes = AzerpostOffice::orderBy('name')->get();
        foreach ($zipcodes as $zipcode) {
            $zipcities[] = $zipcode;
        }

        $azeriexpressoffices = AzeriExpressOffice::whereNotIn('name', ['Sumgait', 'Sheki'])->orderBy('description')->get();
        $deliverypoints = DeliveryPoint::whereNotIn('id',[10,11,12,13,14,15,16,17,18,19,23])->orderBy('description')->get();
        $suratOffices = SuratOffice::orderBy('description')->get();
        $yenipoctOffices = YenipoctOffice::orderBy('description')->get();
        $kargomatOffices = KargomatOffice::orderBy('description')->get();

        $filials = [
        ];

        foreach ($deliverypoints as $deliverypoint) {
            $filials['ase_' . $deliverypoint->id] = "ASE - " . $deliverypoint->description;
        }

        foreach ($azeriexpressoffices as $azexpressoffice) {
            $filials[$azexpressoffice->id] = "Azeriexpress - " . $azexpressoffice->description;
        }

        foreach ($zipcities as $zipcode) {
            $filials['zip_' . $zipcode->name] = "Azerpoct - " . $zipcode->name;
        }

        foreach ($suratOffices as $suratOffice) {
            $filials['surat_' . $suratOffice->id] = "Surat Kargo - " . $suratOffice->description;
        }

        foreach ($yenipoctOffices as $yenipoctOffice) {
            $filials['yp_' . $yenipoctOffice->id] = "Yeni poçt  - " . $yenipoctOffice->description;
        }
        foreach ($kargomatOffices as $kargomatOffice) {
            $filials['kargomat_' . $kargomatOffice->id] = "Kargomat  - " . $kargomatOffice->description;
        }
        foreach ($citiesObj as $city) {
            $cities[] = $city;
        }

        return view('front.auth.register', compact('title', 'hideSideBar', 'hideNavBar', 'bodyClass', 'cities', 'azeriexpressoffices','suratOffices','yenipoctOffices','kargomatOffices', 'deliverypoints', 'zipcities', 'zipcodes', 'filials'));
    }
}
