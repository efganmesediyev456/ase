<?php

namespace App\Http\Controllers\Api\Meest\Package;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use function App\Http\Controllers\Api\Unitrade\Meest\Package\response;


class TrackingController extends Controller
{


    public function __construct()
    {
    }

    public function status(Request $request)
    {
        return response()->json([
            "status" => false,
            "message" => "API is not ready for prod yet!",
            "data" => []
        ], 400);

    }

}
