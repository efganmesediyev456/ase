<?php

namespace App\Http\Controllers\Front\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/user';

    /**
     * Create a new controller instance.
     *
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Show the application's login form.
     *
     * @return Response
     */
    public function showLoginForm()
    {
        $title = 'Login';
        $hideSideBar = $hideNavBar = true;
        $bodyClass = 'login-container login-cover  pace-done';

        return view('front.auth.login', compact('bodyClass', 'hideSideBar', 'hideNavBar', 'title'));
    }

    public function username()
    {
        $field = filter_var(\Request::input('login'), FILTER_VALIDATE_EMAIL) ? 'email' : 'customer_id';
        \Request::merge([$field => \Request::input('login')]);

        return $field;
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        /* Update password */
        $user = User::where($this->username(), $request->input('login'))->first();

        if ($user and $user->old_password) {
            if (md5($request->input('password')) === $user->old_password) {
                $user->password = $request->input('password');
                $user->old_password = null;
                $user->save();
            }
        }

//        if (!$user->verified) {
//            return back()->withErrors([
//                'login' => 'Hesabınız hələ təsdiqlənməyib. Zəhmət olmasa e-poçtunuzu yoxlayın.'
//            ]);
//        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }


    protected function authenticated(Request $request, User $user)
    {
        $user->login_at = Carbon::now();
        $user->save();
    }
}
