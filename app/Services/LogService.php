<?php

namespace App\Services;

use App\Models\Log;


class LogService
{
    /**
     * @var mixed
     */
    private $channel;

    public function log($level, $message, $context = [])
    {
        Log::create([
            'channel' => $this->channel,
            'level' => $level,
            'message' => $message,
            'context' => json_encode($context),
        ]);
    }

    public function channel($channel)
    {
        $this->channel = $channel;
        return $this;
    }
    public function info($message, $context = [])
    {
        $this->log('info', $message, $context);
    }

    public function debug($message, $context = [])
    {
        $this->log('debug', $message, $context);
    }

    public function error($message, $context = [])
    {
        $this->log('error', $message, $context);
    }

    public function warning($message, $context = [])
    {
        $this->log('warning', $message, $context);
    }
}
