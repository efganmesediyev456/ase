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


class LoginController extends Controller
{

    protected $modelName = 'User';


    public function log($message)
    {
        file_put_contents('/var/log/ase_front_api/login.log',  date('Y-m-d H:i:s')." ".$message . "\n", FILE_APPEND);
    }

    public function login()
    {

         $params = Request::all();
         $ip = Request::ip();
         $str = "auth: login from ip:" . $ip;
         $str .= "  data:" . json_encode($params);
         $this->log($str);
         $login=XSSCheck(Request::get('login'));
         $password=XSSCheck(Request::get('password'));
         $user = User::whereRaw("(email = '".$login."' or customer_id='".$login."')")->first();
         if(!$user) {
             $this->log("auth: user ".$login." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'Failed to authorize'
             ],400);
         }

        //if (md5($password) != $courier->password) {
        $username='';
        if(!password_verify($password,$user->password)) {
             $this->log("auth: user ".$login." password failed");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'Failed to authorize'
             ],400);
        }
        if(!$user->remember_token) {
            $uer->remember_token=Str::random(60);
            $user->save();
        }
        return Response::json([
            'status' => 200,
            'result' => 1,
            'token'  => $user->remember_token,
            'message' => 'Ok'
        ],200);
    }

}
