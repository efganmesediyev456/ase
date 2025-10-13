<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Extra\Notification;
use App\Models\Extra\Whatsapp;
use App\Models\NotificationQueue;
use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Services\Sms;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VerificationCodesController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'string|required',
            'text' => 'string|required',
        ]);

        DB::table('verification_codes')->insert([
            'from' => $request->post('phone'),
            'message' => $request->post('text'),
            'created_at' => Carbon::now(),
        ]);

        if (strtolower(trim($request->post('phone'))) == "trendyol") {
            $user = User::where('sms_verification_code_queried_at', '>=', Carbon::now()->subMinutes(3))->first();
            if ($user) {
                $code = $this->filterSms($request->post('text'));
                $template = WhatsappTemplate::where('key', 'trendyol_verification_code')->where('active', 1)->first();
                if($template) {
                    $content['whatsapp'] = clarifyContent($template->content, ['code' => $code]);
                    $content['sms'] = clarifyContent($template->content_sms, ['code' => $code]);

                    NotificationQueue::create([
                        'to' => Whatsapp::clearNumber($user->phone),
                        'content' => json_encode($content),
                        'type' => 'WHATSAPP',
                        'send_for_id' => $user->id,
                    ]);

                    $user->sms_verification_code_queried_at = null;
                    $user->save();
                }
            }
        }
        return response()->json(['success' => true]);
    }

    /**
     * @param $message
     * @return string
     */
    private function filterSms($message): string
    {
        $code = preg_replace('/[^0-9]/', '', $message);
        if (strlen($code) > 6) {
            return mb_substr($code, 0, 6);
        }
        return $code;
    }
}
