<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequestLogger
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $requestDetails = [
            'ips' => $request->getClientIps(),
            'timezone' => time(),
            'headers' => $request->headers->all(),
            'content' => $request->getContent(),
            'user_agent' => $request->headers->get('User-Agent'),
        ];
        
        if (!empty($request->all())) {
            \App\Models\Request::query()->insert([
                'uri' => $request->getUri(),
                'method' => $request->getMethod(),
                'request' => json_encode($requestDetails),
                'body' => json_encode($request->all()),
                'response' => $response->getContent()
            ]);
        }


        return $response;
    }
}
