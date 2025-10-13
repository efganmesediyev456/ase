<?php

namespace App\Http\Controllers\Api;

use Config;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use JWTAuth;
use Request;
use Response;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $limit = 30;
    protected $errorText = null;
    protected $user;

    public function response($data)
    {
        if ($data or ((method_exists($data, "count") and $data->count()))) {
            $statusCode = 200;
            $response = $data;

        } else {
            $response = [
                "error" => $this->errorText,
            ];
            $statusCode = 400;
        }

        return Response::json($response, $statusCode);
    }
}
