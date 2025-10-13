<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CD;
use App\Models\Customer;
use App\Models\Filial;
use App\Models\Package;
use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class CourierLocationController extends Controller
{

    private function checkTokenStatus($token)
    {
        $valid_tokens = [
            'aGTl0ZmMHuDrPvK8Sfp8A8qUzadDZ9IxJ294R0GmcCx9Iheym727h3dGK9CzkHph',
        ];

        return in_array($token, $valid_tokens);
    }

    public function getLocation(Request $request)
    {
        $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
        $website = "https://api.telegram.org/bot" . $botToken;
        $chatId = "-1002397303546";
        file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text=".json_encode($request->all()));

//        $botToken = '7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU';
//        $chatId = '-1002397303546';
//        $text = json_encode($request->all());
//
//        $client = new Client([
//            'base_uri' => 'https://api.telegram.org/',
//        ]);
//
//        $client->request('POST', "bot{$botToken}/sendMessage", [
//            'form_params' => [
//                'chat_id' => $chatId,
//                'text'    => $text,
//            ],
//        ]);

        $header_token = $request->header("token");

        if (!$this->checkTokenStatus($header_token)) {
            return response()->json(["message" => "Unauthorized"], 401);
        }

        $phone = $request->get('phone');

        $latitude  = $request->get('location')['latitude'];
        $longitude  = $request->get('location')['longitude'];

        if (strpos($phone, '994') === 0) {
            $phone = substr($phone, 3);
        } elseif (strpos($phone, '0') === 0) {
            $phone = substr($phone, 1);
        }

        if ($phone){

            $courier_deliveries = CD::whereNull('company_name')
                ->where('phone', 'like', "%{$phone}%")
                ->orderBy('id', 'desc')
                ->whereIn('status',[0,1,2,3,101])
                ->get();

            foreach ($courier_deliveries as $courier){
                $courier->addr_latitude = $latitude;
                $courier->addr_longitude = $longitude;
                $courier->save();
            }

        }

        return response()->json([
            'status' => '200',
            'message' => 'Success.',
        ], 200);

    }
}
