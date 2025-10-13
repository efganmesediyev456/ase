<?php

namespace App\Http\Controllers\Front\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use Hash;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Response;
use App\Models\User;


class UserController extends Controller
{

    protected $modelName = 'User';


    public function log($message)
    {
        file_put_contents('/var/log/ase_front_api/login.log',  date('Y-m-d H:i:s')." ".$message . "\n", FILE_APPEND);
    }

    public function self()
    {
	$user=auth()->guard('user')->user();
	$data['name']=$user->name;
	$data['surname']=$user->surname;
	$data['email']=$user->email;
	$data['customer_id']=$user->customer_id;
	$data['fin']=$user->fin;
	$data['city_id']=$user->city_id;
	$data['passport']=$user->passport;
	$data['zip_code']=$user->zip_code;
	$data['store_status']=$user->store_status;
	$data['azeri_express_use']=$user->azeri_express_use;
	$data['azeri_express_office_id']=$user->azeri_express_office_id;
	$data['azerpoct_send']=$user->azerpoct_send;
        return Response::json([
            'status' => 200,
            'result' => 1,
	    'data'  => $data,
            'message' => 'Ok'
        ],200);
    }

}
