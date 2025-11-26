<?php

namespace App\Http\Controllers\Front;

use App\Http\Requests;
use App\Models\CD;
use App\Models\Track;
use App\Models\AsehubModel;
use App\Models\Extra\Notification;
use App\Models\Extra\SMS;
use DB;
/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Front
 */
class CourierDeliveryController extends MainController
{

    public function err($message) {
        $ldate = date('Y-m-d H:i:s');
        file_put_contents('/var/log/ase_hub_error.log',$ldate." api ".$message."\n",FILE_APPEND);
    }

    public function log($message) {
        $ldate = date('Y-m-d H:i:s');
        file_put_contents('/var/log/ase_hub_api.log', $ldate." ".$message."\n",FILE_APPEND);
    }

    public function success($message) {
        $str=\Response::json([
            'success' => 'true',
            'message' => $message,
        ]);
        return $str;
    }

    public function success_track_add($message) {
        $str=\Response::json([
            'success' => 'true',
            'message' => $message,
            'track_id' => $this->ah->track->id,
            'customer_id' => $this->ah->track->customer_id,
        ]);
        return $str;
    }

    public function failure($message) {
        $str=\Response::json([
            'success' => 'false',
            'message' => $message,
        ],400);
        return $str;
    }

    public function track_add()
    {
        $content = \Request::getContent();
        $params = json_decode($content,false);

        $ip = \Request::ip();
        $ldate = date('Y-m-d H:i:s');
        $str= $ldate."\n"."  POST from ip:".$ip."\n";
        $str.="  data:".$content;
        $this->log($str);
        if(!$this->ah->track_add_from_json($content)) {
            return $this->failure($this->ah->message);
        } else {
            return $this->success_track_add($this->ah->message);
        }
    }

    public function getPay($code)
    {
        $item = CD::where('id', $code)->first();
        if (!$item) {
            abort(404, 'CD not found');
        }
        return view('front.courier_delivery.payment', compact('item'));
    }
}
