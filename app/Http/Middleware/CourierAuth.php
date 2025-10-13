<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;
use App\Models\Courier;
use Auth;

class CourierAuth
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
	$courier = Courier::where('email',$login)->where('remember_token',$token)->first();
	if(!$courier) {
            file_put_contents('/var/log/ase_cd_api.log',   date('Y-m-d H:i:s').' auth: failed ' . $ip . ' ' . $login . "\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 0,
                'message' => 'Authorization failed'
            ], 400);
	}
	Auth::guard('courier')->login($courier);
        return $next($request);
    }
}
