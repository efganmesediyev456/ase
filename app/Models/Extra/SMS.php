<?php

namespace App\Models\Extra;

use App\Models\NotificationQueue;
use App\Models\SMSTemplate;
use App\Models\User;
use Eloquent;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Extra\SMS
 *
 * @mixin Eloquent
 * @method static Builder|SMS newModelQuery()
 * @method static Builder|SMS newQuery()
 * @method static Builder|SMS query()
 */
class SMS extends Model
{
    public static function sendByQueue(NotificationQueue $queue)
    {
        if ($queue && $queue->type == 'SMS') {
            self::getData(self::clearNumber($queue->to), $queue->content, $queue->from);

            return true;
        }

        return false;
    }

    public static function getData($number, $text, $from = NULL)
    {
        try {
            $number = self::clearNumber($number);

            $client = new Client();

            $text = trim(str_replace("\r\n", "", $text));
//            $text = str_replace(
//                ['İ','ə', 'ı', 'ü', 'ö', 'ğ', 'ç', 'ş', 'Ə', 'I', 'Ü', 'Ö', 'Ğ', 'Ç', 'Ş','$','₼'],
//                ['I','e', 'i', 'u', 'o', 'g', 'c', 's', 'E', 'I', 'U', 'O', 'G', 'C', 'S','USD','AZN'],
//                $text
//            );

            $text = str_replace(
                ['İ','ə', 'ı', 'ü', 'ö', 'ğ', 'ç', 'ş', 'Ə', 'I', 'Ü', 'Ö', 'Ğ', 'Ç', 'Ş','$','₼','ș','Ș'],
                ['I','e', 'i', 'u', 'o', 'g', 'c', 's', 'E', 'I', 'U', 'O', 'G', 'C', 'S','USD','AZN','s','S'],
                $text
            );


            $from_str = env('SMS_FROM');
            if ($from) $from_str = $from;


            $request = @$client->get('http://api.msm.az/sendsms?user=' . env('SMS_USER') . '&password=' . env('SMS_PASSWORD') . '&gsm=' . $number . '&from=' . $from_str . '&text=' . $text);
            //dump('http://api.msm.az/sendsms?user=' . env('SMS_USER') . '&password=' . env('SMS_PASSWORD') . '&gsm=' . $number . '&from=' . env('SMS_FROM') . '&text=' . $text);
            echo 'http://api.msm.az/sendsms?user=' . env('SMS_USER') . '&password=' . env('SMS_PASSWORD') . '&gsm=' . $number . '&from=' . env('SMS_FROM') . '&text=' . $text."\n";
            echo $request->getBody();
            parse_str($request->getBody(), $params);
            if (isset($params['errtext']) && $params['errtext'] !== 'OK') {
                $_error = $params['errtext'];
                throw new \Exception($_error);
            }
            return $request->getBody();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() ?? 'Unknown error');
            return ['success' => false, 'error' => $e->getMessage()];
        }

    }

    public static function clearNumber($number)
    {
        $number = explode(";", $number)[0];
        $number = explode(",", $number)[0];
        $number = explode("/", $number)[0];
        $number = explode('\\', $number)[0];
        $number = str_replace(" ", "", $number);
        $number = str_replace("_", "", $number);
        $number = str_replace("-", "", $number);
        $number = str_replace("(", "", $number);
        $number = str_replace(")", "", $number);
        $number = trim($number);
        if (substr($number, 0, 1) === '+') {
            $number = str_replace("+", "", $number);
        }
        if (substr($number, 0, 2) === '00') {
            $number = str_replace("00", "", $number);
        }
        if (substr($number, 0, 3) === '994') {
            $number = substr($number, 3);
        }
        if (substr($number, 0, 2) === '94') {
            $number = substr($number, 2);
        }
        if (strlen($number) == 10 || substr($number, 0, 1) === '0') {
            $number = substr($number, 1);
        }
        $number = preg_replace('/\D/', '', $number);
        if (strlen($number) == 9) {
            $number = "994" . $number;
        }
        if (strlen($number) < 9 || strlen($number) > 12) {
            $number = null;
        }

        return $number;
    }


    public static function sendPureTextByNumber($number, $content)
    {
        NotificationQueue::create([
            'to' => self::clearNumber($number),
            'content' => $content,
            'type' => 'SMS',
        ]);
    }

    public static function sendByNumber($number, $data = [], $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = SMSTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = SMSTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $content = clarifyContent($template->content, $data);

        return self::getData(self::clearNumber($number), $content);
    }

    public static function sendByTrack($track, $data, $templateKey, $templateKey1 = null)
    {
        if (!$track) {
            return false;
        }

        $template = null;
        if ($templateKey1)
            $template = SMSTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = SMSTemplate::where('key', $templateKey)->where('active', 1)->first();

        $phone = $track->customer && $track->customer->phone ? $track->customer->phone : $track->phone;

        if (!$template || !$phone) {
            return false;
        }

        $content = clarifyContent($template->content, $data);
        $id = 0;

        NotificationQueue::create([
            'to' => self::clearNumber($phone),
            'from' => 'ASE EXPRESS',
            'content' => $content,
            'type' => 'SMS',
            'send_for' => 'TRACK',
            'send_for_id' => $track->id,
        ]);

        return true;
    }

    public static function sendByUser($userId, $data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = SMSTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = SMSTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $user = User::find($userId);

        if (!$template || !$user || !$user->phone) {
            return false;
        }

        $content = clarifyContent($template->content, $data);
        $id = (isset($data['id']) ? $data['id'] : uniqid());

        NotificationQueue::create([
            'to' => self::clearNumber($user->phone),
            'content' => $content,
            'type' => 'SMS',
            'send_for_id' => $id,
        ]);

        return true;
    }

    public static function sendToAllUsers($data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = SMSTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = SMSTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $users = User::all();

        $content = clarifyContent($template->content, $data);
        foreach ($users as $user) {
            self::getData(self::clearNumber($user->phone), $content);
        }
    }

    public static function verifyNumber($number, $data)
    {
        $template = SMSTemplate::where('key', 'sms_verify')->where('active', 1)->first();

        if (!$template) {
            return false;
        }
        $content = clarifyContent($template->content, $data);

        return self::getData(self::clearNumber($number), $content);
    }
}
