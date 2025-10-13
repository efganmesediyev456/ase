<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;

class UExpressKey
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
        $headers = collect($request->header())->transform(function ($item) {
            return $item[0];
        });
        file_put_contents('/var/log/ase_uexpress2_auth.log', 'Auth Failed ' . $ip . ' ' . $headers . "\n", FILE_APPEND);
        if (!$key || $key != env('UEXPRESS_WEBHOOK_KEY')) {
            file_put_contents('/var/log/ase_uexpress2_auth.log', 'Auth Failed ' . $ip . ' ' . $key . '!=' . env('UEXPRESS_WEBHOOK_KEY') . "\n", FILE_APPEND);
            return Response::json([
                'status' => 401,
                'result' => 'error',
                'message' => 'Authorization failed'
            ], 401);
        } else {
            file_put_contents('/var/log/ase_uexpress2_auth.log', 'Auth Ok ' . $ip . "\n", FILE_APPEND);
        }

        return $next($request);
    }
}
