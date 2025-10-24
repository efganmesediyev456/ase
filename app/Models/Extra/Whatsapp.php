<?php

namespace App\Models\Extra;

use App\Models\Customer;
use App\Models\NotificationQueue;
use App\Models\User;
use App\Models\WhatsappTemplate;
use App\Services\Saas\SaasService;
use Eloquent;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Extra\Whatsapp
 *
 * @mixin Eloquent
 * @method static Builder|Whatsapp newModelQuery()
 * @method static Builder|Whatsapp newQuery()
 * @method static Builder|Whatsapp query()
 */
class Whatsapp extends Model
{
    public static function sendByQueue(NotificationQueue $queue)
    {
        if ($queue && $queue->type == 'WHATSAPP') {
            $content = json_decode($queue->content, true);
            self::getData(self::clearNumber($queue->to), $content, $queue);

            return true;
        }

        return false;
    }

    public static function sendByQueueNew(NotificationQueue $queue)
    {
        if ($queue && $queue->type == 'WHATSAPP') {
            $content = json_decode($queue->content, true);
            self::sendWhatsappMessage(self::clearNumber($queue->to), $content, $queue);

            return true;
        }

        return false;
    }

    public static function getData($number, $text, $queue = null)
    {
        $number = self::clearNumber($number);

        if (substr($number, 0, 3) !== "994") {
            $number = "994" . $number;
        }

        $saasService = new SaasService($queue, $text);
        $saasService->setTo($number);
        $saasService->setUserName(null);
        $saasService->setUserCode(null);
        $saasService->setMessage($text['whatsapp']);
        $saasService->setSmsMessage($text['sms']);
        $saasService->createMessage();
    }


    private function getExtraMessageContent()
    {
        return 'Ozon
            +994 50 256 00 75
            
            Iherb
            +994 51 205 46 21
            
            UK/USA Ase shop
            +994 50 286 90 94
            
            TR/GE Ase shop
            +994 51 250 08 10
            
            Taobao
            +994 10 232 72 06';
    }
    public function sendWhatsappMessage($number, $text, $queue = null)
    {
        try {
            $user = $queue->send_for == 'TRACK' ? Customer::find($queue->user_id) : User::find($queue->user_id);
            if($queue->send_for == 'TRACK' && !$user->name){
                $fullname = $user->fullname;
            }else{
                $fullname = $user->name . ' ' . $user->surname;
            }
            $number = self::clearNumber($number);
            $postfields = [
                'message' => $text['whatsapp'],
                'phone_number' => $number,
                'full_name' => $fullname ?? null,
            ];
            // we will handle it in MESSAGY
//            if ($extraMessage) {
//                $postfields['extra_message'] = $this->getExtraMessageContent();
//            }
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://mesagy.com/api/v1/whatsapp/send-message',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $postfields,
                CURLOPT_HTTPHEADER => array(
                    'Authorization: msI!1MP2yz3TAAlmMOeQmGiR1IPPclQU',
                    'Content-Type: multipart/form-data'
                ),
            ));

            $response = json_decode(curl_exec($curl));
            curl_close($curl);


            if (isset($response->status) && $response->status == "SENT") {
                $message_id = $response->id ?? null;
                \Illuminate\Support\Facades\DB::table('whatsapp_logs')->insert([
                        'phone'        =>  $number,
                        'message_id'   =>  $message_id ?? null,
                        'text'         =>  $fullname." ".$text['whatsapp'],
                        'response'     =>  json_encode($response),
                        'sended_date'  =>  now()
                    ]
                );
                return ['success' => true, 'message' => 'WP sent to customer'];
            } else {
                \Illuminate\Support\Facades\DB::table('whatsapp_logs')->insert([
                        'phone' => $number,
                        'text' => $fullname . " " . $text['whatsapp'],
                        'response' => json_encode($response),
                        'sended_date' => now()
                    ]
                );
                $_error = 'Unknown error';
                if(isset($response->errors->error)){
                    $_error = $response->errors->error;
                }elseif (isset($response->error)){
                    $_error = $response->error;
                }
              throw new \Exception($_error);
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage() ?? 'Unknown error');
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }


    public static function clearNumber($number, $addPrefix = false, $space = null)
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
            //$number = str_replace("00", "", $number);
	    $number = preg_replace("/^00/", "", $number);
        }

        if (substr($number, 0, 3) === '994') {
            //$number = str_replace("994", "", $number);
	    $number = preg_replace("/^994/", "", $number);
        }

        if (substr($number, 0, 2) === '94') {
            $number = str_replace("94", "", $number);
	    $number = preg_replace("/^94/", "", $number);
        }

        if (strlen($number) == 10 || substr($number, 0, 1) === '0') {
            $number = substr($number, 1);
        }

        $number = preg_replace('/\D/', '', $number);

        if ($addPrefix && strlen($number) == 9) {
            $number = "994" . $space . $number;
        }

        if (strlen($number) < 9) {
            $number = null;
        }

        return $number;
    }

    public static function sendByUser($userId, $data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = WhatsappTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = WhatsappTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $user = User::find($userId);

        if (!$template || !$user || !$user->phone) {
            return false;
        }

        $content['whatsapp'] = clarifyContent($template->content, $data);
        $content['sms'] = clarifyContent($template->content_sms, $data);
        $id = (isset($data['id']) ? $data['id'] : uniqid());

        NotificationQueue::create([
            'to' => self::clearNumber($user->phone),
            'content' => json_encode($content),
            'user_id' => $user->id,
            'type' => 'WHATSAPP',
            'send_for_id' => $id,
        ]);

        return true;
    }

    public static function sendByTrack($track, $data, $templateKey, $templateKey1 = null)
    {
        if (!$track) {
            return false;
        }

        $template = null;
        if ($templateKey1)
            $template = WhatsappTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = WhatsappTemplate::where('key', $templateKey)->where('active', 1)->first();

        $phone = $track->phone ?: ($track->customer && $track->customer->phone ? $track->customer->phone : null);

        if (!$template || !$phone) {
            return false;
        }

        $content['partner_id'] = $track->partner_id;
        $content['whatsapp'] = clarifyContent($template->content, $data);
        $content['sms'] = clarifyContent($template->content_sms, $data);

        NotificationQueue::create([
            'to' => self::clearNumber($phone),
            'from' => 'ASE EXPRESS',
            'user_id' => $track->customer  ? $track->customer->id : null,
            'content' => json_encode($content),
            'type' => 'WHATSAPP',
            'send_for' => 'TRACK',
            'send_for_id' => $track->id,
        ]);

        return true;
    }

    public static function sendToAllUsers($data, $templateKey, $templateKey1 = null)
    {
        $template = null;
        if ($templateKey1)
            $template = WhatsappTemplate::where('key', $templateKey1)->where('active', 1)->first();
        if (!$template)
            $template = WhatsappTemplate::where('key', $templateKey)->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $users = User::all();

        $content['whatsapp'] = clarifyContent($template->content, $data);
        $content['sms'] = clarifyContent($template->content_sms, $data);
        foreach ($users as $user) {
            self::getData(self::clearNumber($user->phone), $content);
        }
    }

    public static function verifyNumber($number, $data)
    {
        $template = WhatsappTemplate::where('key', 'Whatsapp_verify')->where('active', 1)->first();

        if (!$template) {
            return false;
        }

        $content['whatsapp'] = clarifyContent($template->content, $data);
        $content['sms'] = clarifyContent($template->content_sms, $data);
        return self::getData(self::clearNumber($number), $content);
    }
}
