<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class CustomLogServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        $this->app['log']->extend('custom', function ($app, array $config) {
            $logger = new Logger($config['name'] ?? 'custom');

            $path = $config['path'] ?? storage_path('logs/custom.log');
            $level = $config['level'] ?? Logger::DEBUG;

            $logger->pushHandler(new StreamHandler($path, $level));

            return $logger;
        });
    }
}
