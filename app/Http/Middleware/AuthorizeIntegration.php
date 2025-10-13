<?php

namespace App\Http\Middleware;

use Closure;

class AuthorizeIntegration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        if (!$request->hasHeader('ApiKey') || $request->header('ApiKey') != env('INTEGRATION_API_KEY') || $request->header('sign') != "eb032b1ee9a5abb7a5450562be9e2543") {
//            return Response::json([
//                'success' => false,
//                'message' => 'Authorization failed',
//                'data' => []
//            ], 401);
//        }

        return $next($request);
    }
}
