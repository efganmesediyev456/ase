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
use App\Models\City;


class GeneralController extends Controller
{

    protected $modelName = 'User';


    public function log($message)
    {
        file_put_contents('/var/log/ase_front_api/general.log',  date('Y-m-d H:i:s')." ".$message . "\n", FILE_APPEND);
    }

    public function cities()
    {
	$user=auth()->guard('user')->user();
	$lang=XSSCheck(Request::header('lang'));
	$cities = City::select('cities.*','t_az.name as name_az','t_en.name as name_en','t_ru.name as name_ru');
	$cities = $cities->leftJoin('city_translations as t_az', 'cities.id', '=', 't_az.city_id');
	$cities = $cities->leftJoin('city_translations as t_en', 'cities.id', '=', 't_en.city_id');
	$cities = $cities->leftJoin('city_translations as t_ru', 'cities.id', '=', 't_ru.city_id');
	$cities = $cities->orderBy('t_az.name','asc');
	$cities = $cities->orderBy('t_en.name','asc');
	$cities = $cities->orderBy('t_ru.name','asc');
	$cities = $cities->get();
	$data=[];
	foreach($cities as $city) {
	    $name=$city->name_az;
	    if($lang=='ru' && $city->name_ru) $name = $city->name_ru;
	    else if($lang=='en' && $city->name_en) $name = $city->name_en;
	    $data[] = [ 'id' => $city->id , 'name'=> $name ];
	}
        return Response::json([
            'status' => 200,
            'result' => 1,
	    'count' => count($cities),
	    'data'  => $data,
	    'lang'  => $lang,
            'message' => 'Ok'
        ],200);
    }

}
