<?php

namespace App\Models\Extra;

use Bugsnag;
use Exception;
use Illuminate\Support\Facades\Cache;
use Requests;

/**
 * Class Logic
 *
 * @package App\Models\Extra
 */
class Logic
{
    public static function syncParcel($parcelStr)
    {

        try {
            $token = Logic::token();
            //echo "Token: ".$token."\n\n";
            $url = 'https://api.ase.com.tr/liverest/v1/api/GetManifestDetail/';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type:  application/x-www-form-urlencoded',
                'Authorization: Bearer ' . $token,
            ));
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'manifest_code=' . $parcelStr);
            $server_output = curl_exec($ch);
            $decoded = json_decode($server_output, true);
            curl_close($ch);

            if ($decoded['statusCode'] != 200) {
                if (isset($decoded['errors'][0]['errorMessage'])) {
                    preg_match('/ASE[0-9]{13}/', $decoded['errors'][0]['errorMessage'], $cwbFromText);
                } else {
                    $cwbFromText = null;
                }

                if (isset($decoded['errors'][0]['cwb'])) {
                    preg_match('/ASE[0-9]{13}/', $decoded['errors'][0]['cwb'], $cwb);
                } else {
                    $cwb = null;
                }

                return [
                    'status' => 400,
                    'error' => isset($decoded['errors'][0]['errorMessage']) ? $decoded['errors'][0]['errorMessage'] : null,
                    //'cwb'    => $cwb ? $cwb[0] : ($cwbFromText ? $cwbFromText[0] : null),
                    'cwb' => $cwbFromText ? $cwbFromText[0] : ($cwb ? $cwb[0] : null),
                ];
            }
            return [
                'status' => 200,
                'response' => $decoded
            ];
        } catch (Exception $exception) {
            dd($exception);
            Bugsnag::notifyException($exception);
        }
    }

    public static function insertParcel($data = [])
    {

        try {


            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . Logic::token(),
            ];

            $xdata = \GuzzleHttp\json_encode($data);

            //file_put_contents('/var/log/ase_logic_xml.log',$xdata."\n\n      ------------  \n\n",FILE_APPEND);

            $response = Requests::post('https://api.ase.com.tr/LiveRest/V1/api/parcelslist/', $headers, $xdata, [
                'connect_timeout' => 500,
                'timeout' => 500,
                'verify' => false,
            ]);


            if (!isset($response->body)) {
                self::errorTgMessage('insertParcel');

                return false;
            }

//            dd($response->body);

            $decoded = json_decode($response->body, true);

            if ($decoded['statusCode'] != 200) {
                if (isset($decoded['errors'][0]['errorMessage'])) {
                    preg_match('/ASE[0-9]{13}/', $decoded['errors'][0]['errorMessage'], $cwbFromText);
                } else {
                    $cwbFromText = null;
                }

                if (isset($decoded['errors'][0]['cwb'])) {
                    preg_match('/ASE[0-9]{13}/', $decoded['errors'][0]['cwb'], $cwb);
                } else {
                    $cwb = null;
                }

                return [
                    'status' => 400,
                    'error' => isset($decoded['errors'][0]['errorMessage']) ? $decoded['errors'][0]['errorMessage'] : null,
                    //'cwb'    => $cwb ? $cwb[0] : ($cwbFromText ? $cwbFromText[0] : null),
                    'cwb' => $cwbFromText ? $cwbFromText[0] : ($cwb ? $cwb[0] : null),
                ];
            }

            return [
                'status' => 200,
                'response' => $decoded
            ];
        } catch (Exception $exception) {
            dd($exception);
            Bugsnag::notifyException($exception);
        }
    }

    /**
     * @return mixed
     */
    public static function token()
    {
        try {
            //return Cache::remember('logic_token', 600, function () {

            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];

            $data = [
                'grant_type' => 'password',
                'clientId' => '119199',
                'clientSecret' => '16548BCD-B3A9-48F9-8D87-4A4A5931965D',
            ];

            $response = Requests::post('https://api.ase.com.tr/LiveRest/LiveToken/token', $headers, $data, [
                'connect_timeout' => 500,
                'timeout' => 500,
                'verify' => false,
            ]);
            //print_r($response);

            if (!isset($response->body)) {
                self::errorTgMessage('token');

                return false;
            }

            $decoded = json_decode($response->body, true);

            if (!isset($decoded['access_token']) || !$decoded['access_token']) {
                Cache::forget('logic_token');

                return false;
            }

            return $decoded['access_token'];
            // });
        } catch (Exception $exception) {
            Bugsnag::notifyException($exception);
        }
    }

    /**
     * @param string $cwb
     * @return bool|mixed
     */
    public static function closePackage($cwb)
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . Logic::token(),
            ];

            $data = [
                'cwbcode' => $cwb,
            ];
            $response = Requests::post('https://api.ase.com.tr/LiveRest/V1/api/AseShopClose/', $headers, \GuzzleHttp\json_encode($data), [
                'connect_timeout' => 500,
                'timeout' => 500,
                'verify' => false,
            ]);

            if (!isset($response->body)) {
                self::errorTgMessage('closePackage');

                return false;
            }

            $decoded = json_decode($response->body, true);

            if (isset($decoded['statusCode']) && $decoded['statusCode'] == 200 && isset($decoded['result'])) {
                $result = \GuzzleHttp\json_decode($decoded['result'], true);

                return $result;
            }

            return false;
        } catch (Exception $exception) {
            Bugsnag::notifyException($exception);
        }
    }

    public static function closeBasket($cwb)
    {
        try {
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => "Bearer " . Logic::token(),
            ];

            $data = [
                'cwbcode' => $cwb,
            ];

            $response = Requests::post('https://api.ase.com.tr/LiveRest/V1/Basket/CloseBasket', $headers, \GuzzleHttp\json_encode($data), [
                'connect_timeout' => 500,
                'timeout' => 500,
                'verify' => false,
            ]);

            if (!isset($response->body)) {
                self::errorTgMessage('closePackage');

                return false;
            }

            $decoded = json_decode($response->body, true);

            if (isset($decoded['statusCode']) && $decoded['statusCode'] == 200 && isset($decoded['result'])) {
                $result = \GuzzleHttp\json_decode($decoded['result'], true);

                return $result;
            }

            return false;
        } catch (Exception $exception) {
            Bugsnag::notifyException($exception);
        }
    }

    public static function errorTgMessage($function)
    {
        /* Send notification */
        $message = null;
        $message .= "🆘 #AseLogic -de #Function : " . $function . ".";

        sendTGMessage($message);
    }
}
