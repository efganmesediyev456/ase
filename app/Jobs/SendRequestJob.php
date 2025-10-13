<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class SendRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function handle()
    {
        $client = new Client();

        try {
            $response = $client->request($this->request->method, $this->request->uri, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'apitoken' => 'deacf87b2351b4c2395d9b75ef53a879',
                    'x-corezoid-process' => '2793',
                    'x-corezoid-taskid' => '6741b51670451f9ebe9c4ce6',
                    'x-api-key' => 'deacf87b2351b4c2395d9b75ef53a879',
                ],
                'body' => $this->request->body,
            ]);

            if ($response->getStatusCode() === 200) {
                $this->request->response = "DUNE";
            }
        } catch (RequestException $e) {
            $this->request->response = "FAILED: " . $e->getMessage();
        }

        $this->request->save();
    }
}
