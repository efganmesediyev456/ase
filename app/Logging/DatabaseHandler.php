<?php

namespace App\Logging;

use App\Services\LogService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DatabaseHandler extends AbstractProcessingHandler
{
    protected $logService;

    public function __construct(LogService $logService, $level = Logger::DEBUG, bool $bubble = true)
    {
        $this->logService = $logService;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        $this->logService->log($record['level_name'], $record['message'], $record['context']);
    }
}
