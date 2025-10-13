<?php

namespace App\Http\Middleware;

use App\Models\Warehouse;
use Closure;
use Request;
use Response;

class Api
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Request::has('key') and is_string(Request::get('key'))) {

            $warehouse = Warehouse::where('key', Request::get('key'))->first();

            if (!$warehouse) {
                return Response::json(['errors' => 'Key is invalid!'], 401);
            }
        } else {
            return Response::json(['errors' => 'Key required!'], 401);
        }

        $request->request->add(['warehouse' => $warehouse->id]);

        return $next($request);
    }
}
