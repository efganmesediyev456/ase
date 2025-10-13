<?php

namespace App\Services\AzeriExpress;

use App\Services\HttpClient;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class CourierService
{
    protected $client;
    private $token;

    public function __construct(HttpClient $client, AuthService $authService)
    {
        $this->client = $client;
    }

    private function getToken()
    {

        $token = DB::table('tokens')->where('name', 'azeriexpress_token')->first();

        if ($token && $token->expires_at > now()) {
            return $token->access_token;
        }
        $authService = App::make(AuthService::class);
        $data =  $authService->login();

        $token = $data['user']['api_token'];

        DB::table('tokens')->updateOrInsert(
            ['name' => 'azeriexpress_token'],
            [
                'access_token' => $token,
                'expires_at' => now()->addHours(6),
                'updated_at' => now(),
            ]
        );

        return $this->token = $token;
    }

    public function getOrders()
    {
        $token = $this->getToken();
        $url = "https://api.azeriexpress.com/company/orders?token=$token";
        $headers = [
            'Accept-Encoding: application/json',
        ];

        return $this->client->get($url, $headers);
    }

    public function getOrderById($id)
    {
        $token = $this->getToken();
        $url = "https://api.azeriexpress.com/company/orders/$id?token=$token";
        $headers = [
            'Accept-Encoding: application/json',
        ];

        return $this->client->get($url, $headers);
    }

    public function createOrder($data)
    {
        $token = $this->getToken();
        $url = "https://api.azeriexpress.com/company/orders?token=$token";
        $headers = [
            'Accept-Encoding: application/json',
        ];

        return $this->client->post($url, $data, $headers);
    }
}

