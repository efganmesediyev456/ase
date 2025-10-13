<?php

namespace App\Logging;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class CustomLogger
{
    /**
     * Create a custom Monolog instance.
     *
     * @param  array  $config
     * @return \Monolog\Logger
     */
    public function __invoke(array $config)
    {
        $logger = new Logger('custom');

        // Formatter yaradın
        $formatter = new LineFormatter(
            "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n",
            "Y-m-d H:i:s",
            true,
            true
        );

        // File handler əlavə edin
        $handler = new StreamHandler(
            storage_path('logs/custom.log'),
            $config['level'] ?? Logger::DEBUG
        );

        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);

        // Əlavə olaraq rotating file handler da əlavə edə bilərsiniz
        $rotatingHandler = new RotatingFileHandler(
            storage_path('logs/custom-rotating.log'),
            7, // 7 gün saxla
            $config['level'] ?? Logger::DEBUG
        );

        $rotatingHandler->setFormatter($formatter);
        $logger->pushHandler($rotatingHandler);

        return $logger;
    }
}