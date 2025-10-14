<?php

namespace App\Jobs;

use App\Models\TrackStatus;
use App\Services\Integration\UnitradeService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class SendTrackStatusJobLast implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trackStatus;
    protected $trackCode;
    protected $place;
    protected $eventCode;
    protected $token;

    public $tries = 5;

    public function __construct($trackStatus, $trackCode, $place, $eventCode)
    {
        $this->trackStatus = $trackStatus;
        $this->trackCode = $trackCode;
        $this->place = $place;
        $this->eventCode = $eventCode;
        $this->token = $this->getAccessToken();
        $this->client = curl_init();
    }


    private function getAccessToken()
    {
        $token = DB::table('tokens')->where('name', 'api_access_token')->first();

        if ($token && $token->expires_at > now()) {
            return $token->access_token;
        }

        $client = new Client();
        $response = $client->post(UnitradeService::CLIENT_AUTH_URL . '/connect/token', [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'client_id' => UnitradeService::CLIENT_ID,
                'client_secret' => UnitradeService::CLIENT_SECRET,
                'grant_type' => 'client_credentials',
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        $expiresAt = now()->addSeconds($data['expires_in']);

        DB::table('tokens')->updateOrInsert(
            ['name' => 'api_access_token'],
            [
                'access_token' => $data['access_token'],
                'expires_at' => $expiresAt,
                'updated_at' => now(),
            ]
        );

        return $data['access_token'];
    }


    public function handle(): void
    {
        try {
            $uri = UnitradeService::CLIENT_URL . "/v3/tracking/status";

            $body = [[
                "trackNumber" => $this->trackCode,
                "place" => $this->place ?? null,
                "eventCode" => $this->eventCode ?? null,
                "moment" => now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
            ]];

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $uri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token,
                ],
            ]);

            $response = curl_exec($curl);
            $error = curl_error($curl);
            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            curl_close($curl);

            // if ($error || $responseCode !== 200) {
            //     throw new Exception("CURL error: {$error}, HTTP code: {$responseCode}");
            // }

            $decoded = json_decode($response, true) ?? [];

            if (array_key_exists('errors', $decoded)
                and array_key_exists(0, $decoded['errors'])
                and $decoded['errors'][0]['code'] != 'unknown'
            ) {
                
            }else{
                throw new Exception("CURL error: {$error}, HTTP code: {$responseCode}");
            }

            
            $this->trackStatus->update([
                'note' => $response,
            ]);
        } catch (Exception $e) {
            $this->trackStatus->update([
                'note' => 'Error: ' . $e->getMessage(),
            ]);
            throw $e; 
        }
    }
}
