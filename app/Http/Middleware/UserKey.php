<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;
use App\Models\User;
use Auth;

class UserKey
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $login = $request->header('login');
        $token = $request->header('token');
        $ip = $request->ip();
	$user = User::where('email',$login)->where('remember_token',$token)->first();
	if(!$user) {
            file_put_contents('/var/log/ase_front_api/login.log',   date('Y-m-d H:i:s').' auth: failed ' . $ip . ' ' . $login . "\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 0,
                'message' => 'Authorization failed'
            ], 400);
	}
	Auth::guard('user')->login($user);
        return $next($request);
    }
}
