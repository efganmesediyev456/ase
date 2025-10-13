<?php

namespace App\Models\Extra;

use Exception;
use GuzzleHttp\Client;

class Cashback
{

    protected static $url = 'https://bon.az/api/';

    public static function balance()
    {
        return self::curlRequest('balance');
    }

    public static function orders()
    {
        return self::curlRequest('conversions');
    }

    public static function curlRequest($endPoint, $params = [])
    {
        $content = null;
        try {
            $url = self::$url . $endPoint;
            $client = new Client();
            $params['token'] = env('CASHBACK_TOKEN');

            $response = $client->request('GET', $url, ['query' => $params, 'connect_timeout' => 3]);

            $statusCode = $response->getStatusCode();
            $content = \GuzzleHttp\json_decode($response->getBody(), true);
        } catch (Exception $exception) {
        }
        return $content;
    }
}
