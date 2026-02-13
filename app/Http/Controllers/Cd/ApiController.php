<?php

namespace App\Http\Controllers\Cd;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use Hash;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Response;
use App\Models\Track;
use App\Models\CD;
use App\Models\Courier;
use App\Models\CourierLocationLog;

class ApiController extends Controller
{

    protected $modelName = 'CD';


    public function log($message)
    {
	$courier=auth()->guard('courier')->user();
	if($courier)
            file_put_contents('/var/log/ase_cd_api.log',  date('Y-m-d H:i:s')." [".$courier->name."] ".$message . "\n", FILE_APPEND);
	else
            file_put_contents('/var/log/ase_cd_api.log',  date('Y-m-d H:i:s')." ".$message . "\n", FILE_APPEND);
    }

    public function __construct()
    {

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
         $courier = Courier::where('email',$login)->first();
	 if(!$courier) {
             $this->log("auth: user ".$login." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'Failed to authorize'
             ],400);
	 }

	//if (md5($password) != $courier->password) {
	$username='';
	if(!password_verify($password,$courier->password)) {
             $this->log("auth: user ".$login." password failed");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'Failed to authorize'
             ],400);
        }
	if(!$courier->remember_token) {
	    $courier->remember_token=Str::random(60);
	    $courier->save();
	}
        return Response::json([
            'status' => 200,
            'result' => 1,
	    'token'  => $courier->remember_token,
	    'name'   => $courier->name,
            'message' => 'Ok'
        ],200);
    }

    public function cd_set_nd_statuses()
    {
	$this->log('cd_set_nd_statuses '.json_encode(Request::all()));
        $this->validate(Request(), [
            'ids' => 'required|string',
            'value' => 'required|string',
        ]);
        $value=Request::get('value');
	$ids=Request::get('ids');
	$cds=CD::whereIn('id',explode(',',$ids))->get();
	 if(!$cds) {
             $this->log("cd_set_nd_statuses: ".$ids." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'ids not found'
             ],400);
	 }
	foreach($cds as $cd) {
	    $this->log('OK '.$cd->id.' '.$value);
	    $cd->not_delivered_status=$value;
	    $cd->save();
	}
        return Response::json([
            'status' => 200,
            'result' => 1,
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
	$deleted_photo='';
	if($cd->photo) {
	   $deleted_photo=$cd->photo;
	   $cd->photo=NULL;
    	}
	$file->move(public_path('uploads/cd/'), $fileName);

	$cd->photo=$fileName;
	$cd->save();
	   if(!empty($deleted_photo)) {
	       $ph_cds=CD::where('photo',$deleted_photo)->get();
	       if($ph_cds && count($ph_cds)<=0)  {
		   $this->log("Delete ".$deleted_photo);
	           unlink(public_path('uploads/cd/').$deleted_photo);
	       }
	   }
	$this->log('photo_url: '.$cd->photo_url);
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_photos()
    {
        $this->validate(Request(), [
            'ids' => 'required|string',
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
	$ids=Request::get('ids');
	$cds=CD::whereIn('id',explode(',',$ids))->get();
	 if(!$cds) {
             $this->log("cd_set_photos: ".$ids." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'ids not found'
             ],400);
	 }
        $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
	$this->log('cd_photos ids: '.$ids.' file: '.$fileName);
        $file->move(public_path('uploads/cd/'), $fileName);
	foreach($cds as $cd) {
	   $this->log("cd ".$cd->id);
	   $deleted_photo='';
	   if($cd->photo && file_exists(public_path('uploads/cd/').$cd->photo)) {
	      $deleted_photo=$cd->photo;
	      $cd->photo=NULL;
    	   }
	   $cd->photo=$fileName;
	   $cd->save();
	   if(!empty($deleted_photo)) {
	       $ph_cds=CD::where('photo',$deleted_photo)->get();
	       if($ph_cds && count($ph_cds)<=0)  {
		   $this->log("Delete ".$deleted_photo);
	           unlink(public_path('uploads/cd/').$deleted_photo);
	       }
	   }
	   $this->log('id:'.$cd->id.' photo_url: '.$cd->photo_url);
	}
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function ping()
    {
	//$this->log('ping');
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
	$this->log($cd->id." ".$cd->status);
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_set_statuses()
    {
	$this->log('cd_set_status '.json_encode(Request::all()));
        $this->validate(Request(), [
            'ids' => 'required|string',
            'value' => 'required|integer',
        ]);
        $value=Request::get('value');
	$ids=Request::get('ids');
	$cds=CD::whereIn('id',explode(',',$ids))->get();
	 if(!$cds) {
             $this->log("cd_set_statuses: ".$ids." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'ids not found'
             ],400);
	 }
	foreach($cds as $cd) {
	    $cd->status=$value;
	    $cd->save();
	    $this->log($cd->id." ".$cd->status);
	}
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

    public function update_location()
    {
        //$this->log('update_location '.json_encode(Request::all()));
        $this->validate(Request(), [
            'id' => 'required|integer',
            'longitude' => 'required|string',
            'latitude' => 'required|string',
        ]);
        $id=Request::get('id');
        $longitude=Request::get('longitude');
        $latitude=Request::get('latitude');
        $crl=CourierLocationLog::find($id);
	$courier=auth()->guard('courier')->user();
        if($crl) {
            $crl->updated_at=date('Y-m-d H:i:s');
            $crl->save();
	    if($courier->location_log_id != $crl->id) {
                $courier->location_log_id=$crl->id;
                $courier->save();
            }
        }
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function new_location()
    {
        //$this->log('new_location '.json_encode(Request::all()));
        $this->validate(Request(), [
            'longitude' => 'required|string',
            'latitude' => 'required|string',
        ]);
        $courier=auth()->guard('courier')->user();
        $longitude=Request::get('longitude');
        $latitude=Request::get('latitude');
        $crl=new CourierLocationLog();
        $crl->longitude=$longitude;
        $crl->latitude=$latitude;
        $crl->courier_id=$courier->id;
        $crl->save();
        $courier->location_log_id=$crl->id;
        $courier->save();
        return Response::json([
            'status' => 200,
            'result' => $crl->id,
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

    public function cd_set_locations()
    {
	$this->log('cd_set_location '.json_encode(Request::all()));

        $this->validate(Request(), [
            'ids' => 'required|string',
            'longitude' => 'required|string',
            'latitude' => 'required|string',
        ]);
        $longitude=Request::get('longitude');
        $latitude=Request::get('latitude');
	$ids=Request::get('ids');
	$cds=CD::whereIn('id',explode(',',$ids))->get();
	 if(!$cds) {
             $this->log("cd_set_photos: ".$ids." not found");
             return Response::json([
                 'status' => 400,
                 'result' => 0,
                 'message' => 'ids not found'
             ],400);
	 }
	foreach($cds as $cd) {
	    $cd->longitude=$longitude;
	    $cd->latitude=$latitude;
	    $cd->save();
	}
        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_new_vfs()
    {
        $this->log('cd new vfs '.json_encode(Request::all()));
        $this->validate(Request(), [
            'tracking_code' => 'required|string|min:3,max:50',
        ]);
        $courier=auth()->guard('courier')->user();
        $trackingCode=Request::get('tracking_code');
        $track=new Track();
        $track->partner_id=10;
        $track->tracking_code=$trackingCode;

        $cd=CD::newCD($track,$courier->id,3);
        $cd->direction=4;
        $cd->invoice_type=1;
        $cd->company_name='VFS';
        $cd->save();
        $this->log('cd new vfs Ok'.$track->tracking_code.' '.$courier->id);

        return Response::json([
            'status' => 200,
            'result' => 1,
            'message' => 'Ok'
        ],200);
    }

    public function cd_list()
    {

	$this->log('cd list '.json_encode(Request::all()));
	$courier=auth()->guard('courier')->user();
        $arr_status=[3,7];
        $status=Request::get('status');
        $changeStatus=Request::get('change_status');
	$id=Request::get('id');
	$dir=Request::get('dir');
	$cdNamePhone=Request::get('cd_name');
	$cdName='';
	$cdPhone='';
	$data=[];
	if(!empty($cdNamePhone)) {
	   $arr=explode(' -:- ',$cdNamePhone);
	   if(count($arr)>=1) $cdName=$arr[0];
	   if(count($arr)>=2) $cdPhone=$arr[1];
	   $this->log("cdName: ".$cdName." cdPhone: ".$cdPhone);
	}
	$q=Request::get('q');
	$barCode=Request::get('barcode');
        $items = CD::select('courier_deliveries.*');
	$maxCount=NULL;
	if($id) {
	    $maxCount=1;
            $items->where('courier_deliveries.id', $id);
	} else if($barCode) {
	    $maxCount=1;
	    $arr_status[]=1;
	    $arr_status[]=2;
	    $track=Track::where('tracking_code',$barCode)->first();
	    if($track) {

            if (in_array($track->status, [19, 27])) {
//                $track->scanned_at = Carbon::now();
//                $track->save();
                $this->log('Rejected statusunda olan bağlama '.$track->id);
                return response()->json([
                    'status' => 200,
                    'result' => 2,
                    'data'  => $data,
                    'message' => 'Rejected statusunda olan bağlama',
                ]);
            }

	        if($track->debt_price && $track->debt_price>0 && !$track->paid_debt) {
                    $this->log('Debt Price');

                    return Response::json([
                        'status' => 200,
                        'result' => 2,
                        'data'  => $data,
                        'message' => 'Debt Price '.$track->debt_price
                    ],200);
                }
		if(!$track->courier_delivery) {
		    $this->log('no cd');
		    $cd = CD::newCD($track, $courier->id, 2);
		    if($cd) {
			$cd->save();
		    	$this->log('created cd');
			$track->courier_delivery_id=$cd->id;
		    }
		}
	    }
	    if($track && $track->courier_delivery_id) {
		$track->bot_comment="Courier scan at ".date('Y-m-d H:i:s');
		if($track->status==16)
		    $track->status=20;
		$track->save();
                $items->where('courier_deliveries.id', $track->courier_delivery_id);
	    } else {
		$barCode2=str_replace('-','',preg_replace('/\s+/', '',$barCode));
                $items->whereRaw("(courier_deliveries.packages_txt like '%".$barCode."%' or courier_deliveries.packages_txt like '%".$barCode2."%' or (additional_number is not null and LOWER(additional_number) = LOWER('".$barCode."')))");
                $items->whereIn('courier_deliveries.status', $arr_status)->whereRaw('(courier_deliveries.direction != 3 or courier_deliveries.status != 1)');
	    }
	} else {
            if ($q && !empty($q)) {
		$arr_status[]=2;
                //$items = $items->leftJoin('users', 'courier_deliveries.user_id', 'users.id');
                $q = str_replace('"', '', $q);
		$this->log("(packages_txt like '%" . $q . "%')");
		//$this->log("(custom_id like '%" . $q . "%' or packages_txt like '%" . $q . "%' or courier_deliveries.name like '%" . $q . "%' or courier_deliveries.phone like '%" . $q . "%' or user_comment like '%" . $q . "%' or courier_deliveries.address like '%" . $q . "%' or users.customer_id like '%" . $q . "%')");
                //$items->whereRaw("(custom_id like '%" . $q . "%' or packages_txt like '%" . $q . "%' or courier_deliveries.name like '%" . $q . "%' or courier_deliveries.phone like '%" . $q . "%' or user_comment like '%" . $q . "%' or courier_deliveries.address like '%" . $q . "%' or users.customer_id like '%" . $q . "%')");
                $items->whereRaw("(courier_deliveries.packages_txt like '%" . $q . "%')");
            }
            if ($dir != null && $dir>=0) {
                $items->where('courier_deliveries.direction', $dir);
            }
            if (!empty($cdName)) {
                $items->where('courier_deliveries.name', $cdName);
            }
            if (!empty($cdPhone)) {
                $items->where('courier_deliveries.phone', $cdPhone);
            }
            if ($status != null) {
                $items->where('courier_deliveries.courier_id', $courier->id)->where('courier_deliveries.status', $status);
	    } else {
                $items->where('courier_deliveries.courier_id', $courier->id)->whereIn('courier_deliveries.status', $arr_status)->whereRaw('(courier_deliveries.direction != 3 or courier_deliveries.status != 1)');
	    }
	}
	$items = $items->orderBy('courier_deliveries.courier_get_at', 'asc')->orderBy('courier_deliveries.created_at', 'asc');
	if($maxCount)
	  $items=$items->limit($maxCount)->get();
	else
	  $items=$items->get();
	if($items)
	foreach($items as $item) {
	   if(!in_array($item->status,[0,1,2,3,7]) && !$id) {
	      continue;
	   }
	   $cd_changed=false;
	   if($changeStatus || $barCode) {
	       if($barCode) {
	         if(in_array($item->status,[0,1,2])) {
		    $item->status=3;
		    $cd_changed=true;
	         }  
	         if($item->courier_id != $courier->id) {
	 	    $item->courier_id=$courier->id;
		    if($item->status == 3)
		         $item->courier_get_at=date('Y-m-d H:i:s');
		    $cd_changed=true;
	         } 
	       }
	       if($id) {
	         if(in_array($item->status,[0,1,2])) {
		    $item->status=3;
		    $cd_changed=true;
	         }  
	       }
	   }
	   if($cd_changed) {
	     $item->save();
	   }
	   $cd=['id'=>$item->id,'status'=>$item->status,'name'=>$item->name,'phone'=>$item->phone,'address'=>$item->address];
	   $cd['delivery_price']=$item->delivery_price;
	   $cd['packages_str']=$item->packages_with_cells_str;
	   $cd['status_with_label']=$item->status_with_label;
	   //$cd['assigned_at']=$item->courier_get_at;
	   if($item->courier_get_at)
	       $cd['assigned_at']=substr($item->courier_get_at,2);
	   else
	       $cd['assigned_at']='---';
	   $cd['paid']=$item->paid;
	   $cd['direction']=$item->direction_with_label;
	   $cd['invoice_type']=$item->invoice_type_with_label;
	   $cd['user_comment']=$item->user_comment;
	   $cd['courier_comment']=$item->courier_comment;
	   $cd['photo_url']=$item->photo_url;
	   $cd['addr_latitude']=$item->addr_latitude;
           $cd['addr_longitude']=$item->addr_longitude;
	   $data[]=$cd;
	   //$this->log($item->packages_with_cells_str.'  '.$item->courier_assigned_at.'  '.$item->created_at);
	}

	$this->log('Ok '.count($data));

    return Response::json([
            'status' => 200,
            'result' => 1,
	    'data'  => $data,
            'message' => 'Ok'
        ],200);
    }


}
