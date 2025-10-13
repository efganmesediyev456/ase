<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;

class AseMobileKey
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
        $key = $request->header('x-api-key');
        $ip = $request->ip();
        if (!$key || $key != env('ASEMOBILE_WEBHOOK_KEY')) {
            file_put_contents('/var/log/ase_asemobile.log', 'Auth Failed ' . $ip . ' ' . $key . '!=' . env('ASEMOBILE_WEBHOOK_KEY') . "\n", FILE_APPEND);
            return Response::json([
                'status' => 401,
                'result' => 'error',
                'message' => 'Authorization failed'
            ], 401);
        } else {
            //file_put_contents('/var/log/ase_asemobile.log', 'Auth Ok '.$ip."\n",FILE_APPEND);
        }

        return $next($request);
    }
}
