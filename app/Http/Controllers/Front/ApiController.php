<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Response;
use App\Models\CD;

class ApiController extends Controller
{

    protected $modelName = 'CD';

    public function api_login()
    {
         return Response::json([
             'res' => 'Ok',
              'type' => 'POST'
         ]);
    }

}
