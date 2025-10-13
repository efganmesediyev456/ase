<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use Hash;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Response;
use App\Models\Track;
use App\Models\Package;
use App\Models\Admin;

class ApiController extends Controller
{

    protected $modelName = 'Track';


    public function log($message)
    {
	$admin=auth()->guard('admin')->user();
	if($admin)
            file_put_contents('/var/log/ase_admin_api.log',  date('Y-m-d H:i:s')." [".$admin->name."] ".$message . "\n", FILE_APPEND);
	else
            file_put_contents('/var/log/ase_admin_api.log',  date('Y-m-d H:i:s')." ".$message . "\n", FILE_APPEND);
    }

    public function login()
    {

         $params = Request::all();
         $ip = Request::ip();
         $str = "auth: login from ip:" . $ip;
         $str .= "  data:" . json_encode($params);
         $this->log($str);
	 $login=Request::get('login');
	 $password=Request::get('password');
         $admin = Admin::where('email',$login)->first();
	 if(!$admin) {
             $this->log("auth: user ".$login." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'Failed to authorize'
             ],400);
	 }

	//if (md5($password) != $courier->password) {
	$username='';
	if(!password_verify($password,$admin->password)) {
             $this->log("auth: user ".$login." password failed");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'Failed to authorize'
             ],400);
        }
	if(!$admin->remember_token) {
	    $admin->remember_token=Str::random(60);
	    $admin->save();
	}
        return Response::json([
            'status' => 200,
            'result' => 1,
	    'token'  => $admin->remember_token,
	    'name'   => $admin->name,
            'message' => 'Ok'
        ],200);
    }

    public function cd_set_nd_status()
    {
	$this->log('cd_set_nd_status '.json_encode(Request::all()));
        $this->validate(Request(), [
            'id' => 'required|integer',
            'value' => 'required|string',
        ]);
        $value=Request::get('value');
	$id=Request::get('id');
	$cd=CD::find($id);
	 if(!$cd) {
             $this->log("cd set not delivered status: ".$id." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'id not found'
             ],400);
	 }
	$this->log('OK '.$id.' '.$value);
	$cd->not_delivered_status=$value;
	$cd->save();
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_photo()
    {
        $this->validate(Request(), [
            'id' => 'required|integer',
            'file' => 'required|max:30000',
    	]);
	if(!Request::hasFile('file')) {
             $this->log("cd photo: no image");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'id not found'
             ],400);
	}
	$file=Request::file('file');
	$id=Request::get('id');
	$this->log('cd_photo id: '.$id.' file: '.$file->getClientOriginalName());
	$cd=CD::find($id);
	 if(!$cd) {
             $this->log("cd photo: ".$id." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'id not found'
             ],400);
	 }
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
	if($cd->photo) {
	   unlink(public_path('uploads/cd/').$cd->photo);
	   $cd->photo=NULL;
    	}
	$file->move(public_path('uploads/cd/'), $fileName);

	$cd->photo=$fileName;
	$cd->save();
	$this->log('photo_url: '.$cd->photo_url);
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function ping()
    {
	$this->log('ping');
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_set_status()
    {
	$this->log('cd_set_status '.json_encode(Request::all()));
        $this->validate(Request(), [
            'id' => 'required|integer',
            'value' => 'required|integer',
        ]);
        $value=Request::get('value');
	$id=Request::get('id');
	$cd=CD::find($id);
	 if(!$cd) {
             $this->log("cd_set_status: ".$id." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'id not found'
             ],400);
	 }
	$cd->status=$value;
	$cd->save();
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_set_courier_comment()
    {
	$this->log('cd_set_courier_comment '.json_encode(Request::all()));
        $this->validate(Request(), [
            'id' => 'required|integer',
            'value' => 'required|string',
        ]);
        $value=Request::get('value');
	$id=Request::get('id');
	$cd=CD::find($id);
	 if(!$cd) {
             $this->log("cd_set_courier_comment: ".$id." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'id not found'
             ],400);
	 }
	$cd->courier_comment=$value;
	$cd->save();
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_set_location()
    {
	$this->log('cd_set_location '.json_encode(Request::all()));
        $this->validate(Request(), [
            'id' => 'required|integer',
            'longitude' => 'required|string',
            'latitude' => 'required|string',
        ]);
        $longitude=Request::get('longitude');
        $latitude=Request::get('latitude');
	$id=Request::get('id');
	$cd=CD::find($id);
	 if(!$cd) {
             $this->log("cd_set_location: ".$id." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'id not found'
             ],400);
	 }
	$cd->longitude=$longitude;
	$cd->latitude=$latitude;
	$cd->save();
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function track_get()
    {
	$admin=auth()->guard('admin')->user();
	$barCode=Request::get('barcode');
	$this->log('track get '.json_encode(Request::all()));
        $track = Track::select('tracks.*');
        $track = $track->where("tracks.tracking_code",$barCode);
	$track = $track->first();
	$package = NULL;
	$data=[];
	if($track) {
	    $data['id']=$track->id;
	    $data['tracking_num']=$track->tracking_code;
	    $data['courier_name']=NULL;
	    if($track->cell)
	        $data['cell']=$track->cell;
	    else
	        $data['cell']='No';
	    $data['type']='track';
	    $data['courier_name']='';
	    $data['cd_status']='';
	    $data['status']=$track->status_with_label;
	    $data['cd_not_delivered_status']='';
	    if($track->courier_delivery) {
		$data['cd_status']=$track->courier_delivery->status_with_label;
		$data['cd_not_delivered_status']=$track->courier_delivery->not_delivered_status_with_label;
		if($track->courier_delivery->courier) {
	            $data['courier_name']=$track->courier_delivery->courier->name;
		}
	    }
	} else {
           $package = Package::select('packages.*');
           $package = $package->where("packages.custom_id",$barCode);
           $package = $package->orWhere("packages.tracking_code",$barCode);
	   $package = $package->first();
	   if($package) {
	       $data['id']=$package->id;
	       $data['tracking_num']=$package->custom_id;
	       $data['courier_name']='';
	       $data['cd_status']='';
	       $data['cd_not_delivered_status']='';
	       $data['status']=$package->status_with_label;
	       if($package->cell)
	           $data['cell']=$package->cell;
	       else
	           $data['cell']='No';
	       $data['type']='package';
	       if($package->courier_delivery) {
		   $data['cd_status']=$package->courier_delivery->status_with_label;
		   $data['cd_not_delivered_status']=$package->courier_delivery->not_delivered_status_with_label;
		   if($package->courier_delivery->courier) {
	               $data['courier_name']=$package->courier_delivery->courier->name;
		   }
	       }
	   }
	}	
	if(!$track && !$package) {
	   $this->log('track get '.$barCode.' Failed');
           return Response::json([
               'status' => 400,
               'result' => 0,
               'message' => $barCode.' not found'
           ],400);
	}
	$this->log('track get '.$barCode.' Ok');
        return Response::json([
            'status' => 200,
            'result' => 1,
	    'data'  => $data,
            'message' => 'Ok'
        ],200);
    }

    public function track_cell()
    {
	$admin=auth()->guard('admin')->user();
	$barCode=Request::get('barcode');
	$cell = findCell($barCode);
	$trackingCode=Request::get('tracking_num');
	if(empty($cell)) {
	   $this->log('track cell '.$trackingCode.'  '.$barCode.' Failed');
           return Response::json([
               'status' => 400,
               'result' => 0,
               'message' => 'Cell '.$barCode.' not found'
           ],400);
	}
	$cd=NULL;
	$this->log('track cell '.json_encode(Request::all()));
        $track = Track::select('tracks.*');
        $track = $track->where("tracks.tracking_code",$trackingCode);
	$track = $track->first();
	$package = NULL;
	$data=[];
	if($track) {
	    $track->cell=$cell;
	    $track->status=20;
	    $track->bot_comment='Courier returned';
	    $track->save();
	    $cd=$track->courier_delivery;
	} else {
           $package = Package::select('packages.*');
           $package = $package->where("packages.custom_id",$trackingCode);
	   $package = $package->first();
	   $cd=$package->courier_delivery;
	   if($package) {
	       $package->cell=$cell;
	       $package->status=8;
	       $package->bot_comment='Courier returned';
	       $package->save();
	   }
	}	
	if(!$track && !$package) {
	   $this->log('track cell '.$trackingCode.'  '.$barCode.' Failed');
           return Response::json([
               'status' => 400,
               'result' => 0,
               'message' => 'Track not found'
           ],400);
	}
	if($cd) {
	    $cd->status=2;
	    $cd->returned_at=date('Y-m-d H:i:s');
	    $cd->save();
	}
	$this->log('track cell '.$trackingCode.'  '.$barCode.' Ok');
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Cell set to '.$cell,
        ],200);
    }

}
