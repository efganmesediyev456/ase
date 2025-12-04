<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ProcessCustomTrackAction;
use App\Models\Track;
use App\Models\TrackStatus;
use App\Models\BulkResendStatusLog;
use App\Services\Integration\UnitradeService;
use App\Services\Package\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use DB;
use GuzzleHttp\Client;

class BulkResendStatusController extends \App\Http\Controllers\Controller
{

    private $token;

    private $logTypes = [
        "success",
        "error"
    ];

    public function index()
    {
        $statuses = config('ase.attributes.track.statusShort');
        $logTypes = $this->logTypes;

        $query = BulkResendStatusLog::query();

        if (request('log_type')) {
            $query->where('log_type', request('log_type'));
        }

        if (request('executed_at_from')) {
            $query->where('executed_at', '>=', request('executed_at_from'));
        }

        if (request('executed_at_to')) {
            $query->where('executed_at', '<=', request('executed_at_to'));
        }

        if (request('tracking_code')) {
            $query->where('tracking_code', 'like', '%' . request('tracking_code') . '%');
        }


        if (request('status')) {
            $query->where('status', request('status'));
        }

        $logs = $query->orderBy('executed_at', 'desc')->paginate(20);


        return view('admin.bulk_resend.index', compact('statuses', 'logs', 'logTypes'));
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


    private function logError(
        Track $track,
              $trackStatus = null,
              $errorMessage,
              $logType = 'error',
              $httpCode = null,
              $requestBody = null,
              $responseBody = null
    )
    {
        BulkResendStatusLog::create([
            'track_id' => $track->id,
            'track_status_id' => optional($trackStatus)->id,
            'tracking_code' => $track->tracking_code,
            'http_code' => $httpCode,
            'request_body' => $requestBody,
            'response_body' => $responseBody,
            'error_message' => $errorMessage,
            'log_type' => $logType,
            'user_id' => auth()->id() ?? 1,
            'executed_at' => now(),
        ]);

        $track->error_txt = ($track->error_txt ?? '') . "| " . $errorMessage . " ";
        $track->save();
    }


    private function logSuccess(
        Track       $track,
        TrackStatus $trackStatus,
                    $status,
                    $statusString,
                    $place,
                    $eventCode,
                    $httpCode,
                    $requestBody,
                    $responseBody
    )
    {
        BulkResendStatusLog::create([
            'track_id' => $track->id,
            'track_status_id' => $trackStatus->id,
            'tracking_code' => $track->tracking_code,
            'status' => $status,
            'status_string' => $statusString,
            'place' => $place,
            'event_code' => $eventCode,
            'http_code' => $httpCode,
            'request_body' => $requestBody,
            'response_body' => $responseBody,
            'log_type' => 'success',
            'user_id' => auth()->id() ?? 1,
            'executed_at' => now(),
        ]);
    }

    public function updateStatus(Track $track, $status = null, $date = null)
    {

        $this->token = $this->getAccessToken();

        if (empty($this->token)) {
            $this->logError($track, null, 'Empty token received', 'error');
            return false;
        }
        if ($status == 16) {
            $this->updateStatus($track, 24, $date);
        }
        $client = new Client();
        if ($status) {
            $statusString = array_search((int)$status, UnitradeService::STATES, true);
        } else {
            $statusString = array_search($track->status, UnitradeService::STATES, true);
        }
        if ($statusString === false || ($statusString && !array_key_exists($statusString, UnitradeService::STATE_MAP))) {
            $this->logError(
                $track,
                null,
                "Status not found: $status | " . $track->status . " | $statusString",
                'error'
            );
            return false;
        }
        try {
            $trackStatus = TrackStatus::query()->create([
                'track_id' => $track->id,
                'user_id' => auth()->id() ?? 1,
                'status' => $status ?: $track->status,
                'note' => null,
            ]);

            $headers = [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ];

            $uri = UnitradeService::CLIENT_URL . "/v3/tracking/status";
            $place = UnitradeService::PLACE[$statusString];
            $eventCode = UnitradeService::STATE_MAP[$statusString];

            $body = [[
                "trackNumber" => $track->tracking_code,
                "place" => $place,
                "eventCode" => $eventCode,
                "moment" => now('UTC')->format('Y-m-d\TH:i:s.v\Z')
            ]];

            $requestBody = json_encode($body);


            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $uri,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $this->token
                ),
            ));


            $response = curl_exec($curl);

            $responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            if ($responseCode >= 200 && $responseCode < 300) {

                $trackStatus->update(['note' => $response]);
                $this->logSuccess(
                    $track,
                    $trackStatus,
                    $status ?: $track->status,
                    $statusString,
                    $place,
                    $eventCode,
                    $responseCode,
                    $requestBody,
                    $response
                );
                return true;
            } else {
                $this->logError(
                    $track,
                    $trackStatus,
                    "HTTP Error: $responseCode - $response",
                    'error',
                    $responseCode,
                    $requestBody,
                    $response
                );
                $trackStatus->update(['note' => $response]);
                return false;
            }


        } catch (Exception $e) {
            $this->logError(
                $track,
                null,
                $e->getMessage(),
                'error'
            );
            return false;
        }
    }


    public function store(Request $request)
    {

        $set_tracks = $request->requestText;
        $status = $request->status;

        if ($set_tracks) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim($set_tracks));

            $tracks = Track::whereIn('tracking_code', $tracking_codes)->get();

            if ($tracks->isEmpty()) {
                return \Response::json(['message' => 'No tracks found for the provided codes']);
            }

            foreach ($tracks as $track) {
                if (!$track) {
                    return \Response::json(['message' => 'Track not found']);
                }
                $this->updateStatus(
                    $track,
                    $status,
                    $request->has('date') ? $request->date : null
                );
            }
        }

        return back()->with('success', 'Əməliyyat yerinə yetirildi.');
    }


}