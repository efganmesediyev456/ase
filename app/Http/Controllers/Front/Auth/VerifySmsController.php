<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use App\Models\Extra\SMS;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class VerifySmsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->setting = Setting::find(1);
//        $this->middleware('auth'); bu lazimdi
        $this->middleware('auth', ['throttle' => ['limit' => 2]]);
    }

    public function showResendVerificationSmsForm()
    {
        $user = Auth::user();

        if ($user->sms_verification_status) {
            return redirect('/');
        }

        return view('front.user.smsresend', ['verified' => $user->sms_verification_status, 'phone' => $user->phone]);
    }

    public function sendVerificationSms(Request $request)
    {
        $user = Auth::user();

        if (Auth::user()->sms_verification_status) {
            return redirect('/');
        }

        $this->validate($request, [
            'phone' => 'required|unique:users,phone,' . $user->id,
        ]);

        return $this->verifyAfterEmail($request->phone);
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

    public function getCode()
    {
        $user = Auth::user();

        return view('front.user.smsverify', ['verified' => $user->sms_verification_status, 'number' => $user->phone]);
    }

    public function verify(Request $request)
    {

        $this->validate($request, [
            'sms_verification_code' => 'required|numeric',
        ]);

        $user = User::find(Auth::user()->id);

        if ($user->sms_verification_status) {
            return redirect('/');
        }

        $code = $request->sms_verification_code;

        if ($code == $user->sms_verification_code) {
            $user->sms_verification_status = true;
            $user->save();
            Session::flash('success', trans('smsverification.done'));

            SMS::sendByNumber($user->phone, [], 'registration');
        } else {
            Session::flash('error', trans('smsverification.error'));
        }

        return redirect()->to('/register/verify/resend');
    }

    public function verifyAfterEmail($number = false)
    {
        $user = Auth::user();

        $num = $number ?: $user->phone;

        $user->phone = $num;
        $user->sms_verification_code = rand(1000, 9999);
        $user->save();

        $this->send();

        return redirect('/number/verify/code');
    }
}
