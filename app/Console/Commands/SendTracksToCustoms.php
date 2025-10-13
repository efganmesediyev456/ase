<?php

namespace App\Console\Commands;

use App\Services\Customs\CustomsService;
use Illuminate\Console\Command;

class SendTracksToCustoms extends Command
{
    protected $signature = 'customs:send-tracks';

    protected $description = 'Sends Tracks(weight exists) to Customs';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $service = new CustomsService();
        $service->sendBundlesToCustoms();
    }
}
