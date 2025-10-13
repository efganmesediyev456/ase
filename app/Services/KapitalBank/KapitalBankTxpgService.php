<?php

namespace App\Services\KapitalBank;


class KapitalBankTxpgService
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = config('services.kapital_bank.base_url');
        $this->username = config('services.kapital_bank.username');
        $this->password = config('services.kapital_bank.password');
    }

    protected function getAuthHeader()
    {
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    protected function curlRequest($url, $method = 'GET', $data = null)
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . $this->getAuthHeader(),
                'Content-Type: application/json'
            ],
        ]);

        if ($data !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            throw new \Exception('Curl error: ' . curl_error($curl));
        }

        curl_close($curl);

        return json_decode($response, true);
    }

    public function createOrder(array $orderData)
    {
//        $data = [
//            'order' => [
//                'typeRid' => $orderData['type'] ?? 'Order_SMS',
//                'amount' => $orderData['amount'],
//                'currency' => 'AZN',
//                'language' => $orderData['language'] ?? 'az',
//                'description' => $orderData['description'],
//                'hppRedirectUrl' => $orderData['redirectUrl']
//            ],
//        ];
        $response = $this->curlRequest($this->baseUrl . '/order', 'POST', $orderData);
        if (isset($response['order']['id']) && isset($response['order']['password'])) {
            // Store order details in your database if needed
            // ...

            // Prepare the HPP URL
            $hppUrl = config('services.kapital_bank.hpp_url');
            $redirectUrl = "{$hppUrl}?id={$response['order']['id']}&password={$response['order']['password']}";
            return ['redirectUrl' =>$redirectUrl,'order_id' =>$response['order']['id'],'password' => $response['order']['password']];
        } else {
            return ['redirectUrl' =>null,'order_id' => null,'password' => null];
        }

    }

    public function setSourceToken(array $cardData)
    {
        $data = [
            'token' => [
                'card' => [
                    'panBlock' => [
                        'data' => $cardData['pan'],
                    ],
                    'entryMode' => 'ECommerce',
                    'expiration' => $cardData['expiration'], // Format: MMYY
                ],
            ],
        ];

        return $this->curlRequest($this->baseUrl . '/tokens', 'POST', $data);
    }

    public function process3DSAuth(array $aReqData, string $orderId)
    {
        return $this->curlRequest($this->baseUrl . "/orders/{$orderId}/3ds", 'POST', ['aReq' => $aReqData]);
    }

    public function finalizeTransaction(string $orderId, string $amount, string $cvv = null)
    {
        $transactionData = [
            'tran' => [
                'phase' => 'Single',
                'amount' => $amount,
            ],
        ];

        if ($cvv) {
            $transactionData['tran']['authentication'] = [
                'cvv2Block' => [
                    'data' => $cvv,
                ],
            ];
        }

        return $this->curlRequest($this->baseUrl . "/orders/{$orderId}/finalize", 'POST', $transactionData);
    }

    public function getOrderStatus(string $orderId)
    {
        return $this->curlRequest($this->baseUrl . "/order/{$orderId}", 'GET');
    }

    public function detailed($orderId){
        $this->username = 'TerminalSys/E1020064';
        $this->password = 'pkA]Vuz*:)o0zo#m0$zS';
        $response = $this->curlRequest($this->baseUrl . "/order/{$orderId}?tranDetailLevel=2&tokenDetailLevel=2&orderDetailLevel=2", 'GET');

        $uId        = isset($response['order']['storedTokens'][0]['ridBycofp']) ? $response['order']['storedTokens'][0]['ridBycofp'] : null;
        $cardNumber = isset($response['order']['srcToken']['displayName']) ? $response['order']['srcToken']['displayName'] : null;
        $save       = false;
        if($uId && $cardNumber){
            $save = true;
        }

        return ['uId' => $uId,'cardNumber' => $cardNumber,'save'=>$save] ;
    }
    private function payWithSaveCard($orderId,$orderPassword,$uId){

        $data = [
            "order" => [
                "initiationEnvKind" => "Server"
            ],
            "token" => [
                "extCof" => [
                    "ridByCofp" => $uId,
                    "cofProviderRid" => "TWO_COF"
                ],
                "card" => [
                    "entryMode" => "ECommerce"
                ]
            ]
        ];
        $response   = $this->curlRequest($this->baseUrl . "/order/{$orderId}/set-src-token?password={$orderPassword}", 'POST', $data);
        $readyToPay = false;
        $cardNumber = null;

        if(isset($response['order']['status']) && $response['order']['status'] == 'Preparing' && isset($response['order']['srcToken']['displayName'])){
            $readyToPay = true;
            $cardNumber = $response['order']['srcToken']['displayName'];
        }

        return ['cardNumber' =>$cardNumber,'readyToPay' =>$readyToPay];
    }
    private function payWithSaveCardApprove($orderId,$orderPassword,$amount){

        $data = [
            "tran" => [
                "phase" => "Single",
                "amount" => $amount,
                "conditions" => [
                    "cofUsage" => "Recurring"
                ]
            ]
        ];

        return   $this->curlRequest($this->baseUrl . "/order/{$orderId}/exec-tran?password={$orderPassword}", 'POST', $data);

    }
    public function createOrderForSaveCard(array $orderData,$uId)
    {

        $orderId       = null;
        $orderPassword = null;
        $data = [
            'order' => [
                'typeRid' => $orderData['type'] ?? 'Order_REC',
                'amount' =>  $orderData['amount'],
                'currency' => 'AZN',
                'language' => $orderData['language'] ?? 'az',
                'description' => $orderData['description']
            ]
        ];

        $response = $this->curlRequest($this->baseUrl . '/order', 'POST', $data);


        if (isset($response['order']['id']) && isset($response['order']['password'])) {
            $orderId       = $response['order']['id'];
            $orderPassword = $response['order']['password'];
            $payWithSaveCard = $this->payWithSaveCard($orderId,$orderPassword,$uId);
            if($payWithSaveCard['readyToPay']){
                $payWithSaveCardApprove = $this->payWithSaveCardApprove($orderId,$orderPassword,$orderData['amount']);
                if(isset($payWithSaveCardApprove['tran']['approvalCode'])){
                    return [
                        'status'   => 'success',
                        'order_id' => $orderId,
                        'password' => $orderPassword,
                        'response' => $payWithSaveCardApprove
                    ];
                }
                return ['status'=> 'failed','order_id' => $orderId,'password' => $orderPassword,'response' =>$payWithSaveCardApprove];
            }
        } else {
            return ['status'=> 'failed','order_id' => $orderId,'password' => $orderPassword,'response' =>$response];
        }
    }
}
