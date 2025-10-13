<?php

namespace App\Console\Commands;

use App\Jobs\SendRequestJob;
use App\Models\Request;
use Illuminate\Console\Command;

class SendFailedRequests extends Command
{
    protected $signature = 'send:failed-requests';

    protected $description = 'Command description';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Sending failed requests...');
        $requests = Request::query()->where('response', '000')->get();
        $this->info('Requests count: ' . count($requests));

        foreach ($requests as $request) {
            SendRequestJob::dispatch($request);
        }
    }
}
