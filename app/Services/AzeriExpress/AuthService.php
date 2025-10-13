<?php

namespace App\Services\AzeriExpress;

use App\Models\Log;
use App\Services\HttpClient;

class AuthService
{
    protected $client;

    private $email = "delivery@ase.az";
    private $password = "ase@azex!delivery2024";

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function login()
    {
        $url = 'https://api.azeriexpress.com/login';
        $data = [
            'email' => $this->email,// ?: env('AZERIEXPRESS_EMAIL'),
            'password' => $this->password// ?: env('AZERIEXPRESS_PASSWORD'),
        ];
        $headers = [
            'Accept-Encoding: application/json',
        ];

        $response = $this->client->post($url, $data, $headers);
        Log::create([
            'level' => 'debug',
            'message' => 'Azeriexpress Courier Login',
            'context' => json_encode($response),
        ]);

        return $response;
    }
}

