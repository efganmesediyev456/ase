<?php

namespace App\Services\Saas;

use App\Models\ScGood;
use App\Repositories\PackageRepository;
use GuzzleHttp\Client;
use Log;

class SaasService
{
    private $apiUrl = "https://whatsapp.saas.az/api/v3";
    private $appKey;
    private $authKey;

    private $to;

    private $message;
    private $smsMessage;
    /**
     * @var mixed
     */
    private $userName;
    /**
     * @var mixed
     */
    private $userCode;

    public function __construct($queue = null, $text = [])
    {
        if ($queue && $queue->send_for == "TRACK") {
            $this->appKey = env('SAAS_IHERB_APP_KEY');
            $this->authKey = env('SAAS_IHERB_AUTH_KEY');

            if (array_key_exists('partner_id', $text) && $text['partner_id'] == 8) {
                $this->appKey = env('SAAS_GFS_APP_KEY');
                $this->authKey = env('SAAS_GFS_AUTH_KEY');
            }

            if (array_key_exists('partner_id', $text) && $text['partner_id'] == 3) {
                $this->appKey = env('SAAS_OZON_APP_KEY');
                $this->authKey = env('SAAS_OZON_AUTH_KEY');
            }
        } else {
            $this->appKey = env('SAAS_APP_KEY');
            $this->authKey = env('SAAS_AUTH_KEY');
        }
    }

    public function createMessage()
    {
        Log::channel('saas')->info("createMessage", [$this->appKey, $this->authKey, $this->to, $this->message]);
        $client = new Client();
        $response = $client->post($this->apiUrl . "/message", [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'apiKey' => $this->authKey,
                'message' => [
                    'uuid' => null,
                    'type' => 'text',
                    'content' => [
                        'body' => $this->message
                    ]
                ],
                'recipient' => [
                    'name' => $this->userName,
                    'code' => $this->userCode,
                    'phoneNumber' => $this->to
                ],
                'sms' => [
                    'send' => false,
                    'checkWhatsapp' => true,
                    'body' => $this->smsMessage
                ]
            ],
        ]);
        $statusCode = $response->getStatusCode();
        $responseData = json_decode($response->getBody(), true);
        if ($statusCode === 201) {
//            Log::channel('saas')->info("Messages sent successfully", [[$this->to, $this->message], $responseData]);
        } else {
            echo "Error\n";
            print_r($responseData);
            Log::channel('saas')->error("Messages not sent", [[$this->to, $this->message], $responseData, $this->authKey]);
        }
    }

    /**
     * @return array
     */
    public function createMessages(): array
    {
        return [];
    }

    /**
     * @param mixed $to
     */
    public function setTo($to): void
    {
        $this->to = $to;
    }

    /**
     * @param mixed $message
     */
    public function setMessage($message): void
    {
        $this->message = $message;
    }

    public function setUserName($fullName)
    {
        $this->userName = $fullName;
    }

    public function setUserCode($id)
    {
        $this->userCode = $id;
    }

    /**
     * @param mixed $smsMessage
     */
    public function setSmsMessage($smsMessage): void
    {
        $this->smsMessage = $smsMessage;
    }
}
