<?php

namespace App\Models\Extra;

use App\Models\MobileTemplate;
use App\Models\NotificationQueue;
use App\Models\User;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Extra\Mobile
 *
 */
class Mobile extends Model
{

    public static function getData($user_id, $text)
    {
        $MOBILE_URL = 'https://fcm.googleapis.com/fcm/send';
        $MOBILE_KEY = 'AAAALC21t2Q:APA91bG-yJ6mjNNjC5BxtyVtWyJU3fE_zpkPR8FCI4Amr1MGdkFb5WFw-_XsLjsVYc28SRIl_Y5Wf6M-80Qq8FsTAtc2-xob6HSp1NY-OGsusCgcv1vk75P8MoBlsXsogSMhoQEFANZn';
        $total_res = false;
        $user_devices = DB::select('select distinct fcm_token from user_devices where user_id=? and deleted_at is null order by id desc limit 5', [$user_id]);
        $user_devices = [];
        try {
            $user_devices = DB::select('select distinct fcm_token from user_devices where user_id=? and deleted_at is null order by id desc limit 5', [$user_id]);
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
        }
        foreach ($user_devices as $user_device) {
            $ch = curl_init();
            /*$str="{\n";
	        $str.='  "registration_ids" : ['."\n";
	        $str.='    "'.$user_device->fcm_token.'"'."\n";
		$str.='  ],'."\n";
	        $str.='  "notification" : {'."\n";
	        $str.='    "title":"'.$text.'"'.",\n";
	        $str.='    "text":"'.$text.'"'."\n";
		$str.='  }'."\n";
		$str.="}\n";*/
            $arr = [];
            $arr['registration_ids'] = [$user_device->fcm_token];
            $arr['notification']['title'] = $text;
            $arr['notification']['body'] = $text;
            $str = json_encode($arr);
            //echo $MOBILE_URL."\n";
            //echo $str."\n";
            //return;
            //curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_URL, $MOBILE_URL);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);

            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'accept: */*',
                'Authorization: key=' . $MOBILE_KEY,
                "Content-Type: application/json"
            ));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
            $output = curl_exec($ch);
            //echo $str."\n";
            //echo $output."\n";
            curl_close($ch);
            $res = json_decode($output);
            if ($res->success)
                $total_res = true;
        }

        return $total_res;
    }

    public static function sendByQueue(NotificationQueue $queue)
    {
        if ($queue && $queue->type == 'MOBILE') {
            self::getData($queue->to, $queue->content);

            return true;
        }

        return false;
    }

    public static function sendPureTextByUserId($user_id, $content)
    {
        NotificationQueue::create([
            'to' => $user_id,
            'content' => $content,
            'type' => 'MOBILE',
        ]);
    }

    public static function sendByUserId($user_id, $data = [], $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = MobileTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = MobileTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $content = clarifyContent($template->content, $data);

        return self::getData($user_id, $content);
    }

    public static function sendByUser($userId, $data, $templateKey, $templateKey1 = null)
    {
        //DebugBar::info('Mobile sendByUser 1');
        $template = null;
        if ($templateKey1)
            $template = MobileTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = MobileTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $user = User::find($userId);

        if (!$template || !$user) {
            return false;
        }

        $content = clarifyContent($template->content, $data);
        $id = (isset($data['id']) ? $data['id'] : uniqid());

        NotificationQueue::create([
            'to' => $userId,
            'content' => $content,
            'type' => 'MOBILE',
            'send_for_id' => $id,
        ]);

        return true;
    }

    public static function sendToAllUsers($data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = MobileTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = MobileTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $users = User::all();

        $content = clarifyContent($template->content, $data);
        foreach ($users as $user) {
            self::getData($user->id, $content);
        }
    }
}
