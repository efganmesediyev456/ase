<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Response;

class AuthorizeMeest
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
        if (!$request->hasHeader('ApiKey') || $request->header('ApiKey') != env('MEEST_API_KEY')) {
            return Response::json([
                'success' => false,
                'message' => 'Authorization failed',
                'data' => []
            ], 401);
        }

        return $next($request);
    }
}
