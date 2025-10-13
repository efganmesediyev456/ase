<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;

class AzerPoctKey
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
        $azerpoctKey = $request->header('x-api-key');
        $ip = $request->ip();
        if (!$azerpoctKey || $azerpoctKey != env('AZERPOCT_WEBHOOK_KEY')) {
            file_put_contents('/var/log/ase_azerpoct.log', 'Auth Failed ' . $ip . ' ' . $azerpoctKey . '!=' . env('AZERPOCT_WEBHOOK_KEY') . "\n", FILE_APPEND);
            return Response::json(['result' => 'failed', 'error' => 'Authorization failed', 'message' => 'Authorization failed'], 401);
        } else {
            file_put_contents('/var/log/ase_azerpoct.log', 'Auth Ok ' . $ip . "\n", FILE_APPEND);
        }

        return $next($request);
    }
}
