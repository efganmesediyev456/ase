<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;

class AsehubKey
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
        $asehubKey = $request->header('secret');
        if (!$asehubKey || $asehubKey != env('ASEHUB_WEBHOOK_KEY')) {
            return Response::json(['success' => 'false', 'error' => 'Authorization failed', 'message' => 'Authorization failed'], 401);
        }

        return $next($request);
    }
}
