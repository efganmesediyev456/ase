<?php

namespace App\Logging;

use App\Services\LogService;
use Monolog\Logger;

class DatabaseLogger
{
    public function __invoke(array $config)
    {
        return new Logger('database', [new DatabaseHandler(new LogService())]);
    }
}
