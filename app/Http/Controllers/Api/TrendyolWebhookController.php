<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extra\SMS;
use Illuminate\Http\Request;
use App\Models\TrNumber;


class TrendyolWebhookController extends Controller
{
    public function posted(Request $request, $api_key)
    {
        $data = $request->all();

        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }
        sendTelegramMessage(now().' 🔄 '.json_encode($data));

        if (!$data) {
            return response('OK', 200);
        }

        $text  = $data['text']  ?? 'N/A';
        $sim   = $data['sim']   ?? 'N/A';
        $phone = $data['phone'] ?? null;

        if (!$phone || strtolower($phone) !== 'trendyol') {
            return response('OK', 200);
        }

//        if (
//            stripos($text, 'trendyol adres degisikligi icin dogrulama kodunuz') === false &&
//            stripos($text, 'trendyol icin dogrulama kodunuz') === false
//        ) {
//            return response('OK', 200);
//        }

        $trNumber = TrNumber::where('sim_number', $sim)
            ->where('api_url', $api_key)
            ->first();

        if ($trNumber && $trNumber->assignedUser) {
            $user = $trNumber->assignedUser;

            $phoneToSend = $user->confirmation_phone_number ?? $user->phone;

//            app('notification')->sendOnlySms($phoneToSend, $text);
            try{
                SMS::getData($phoneToSend, $text);
                sendTelegramMessage(now().' ✅ trendyol message is sent text:'.$text.' , phone number:'.$phoneToSend);

            }catch (\Exception $exception){
                sendTelegramMessage(now().' ❌ trendyol message didnt send text:'.$text.' , phone number:'.$phoneToSend.' ,message:'.$exception->getMessage());
            }


            // reset et
            $trNumber->assigned_user_id = null;
            $trNumber->assigned_at = null;
            $trNumber->save();
        }

        return response('ok', 200);
    }
}


