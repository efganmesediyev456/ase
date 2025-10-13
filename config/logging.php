<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |
    */

    'channels' => [

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => 'debug',
            'days' => 14,
        ],
//        'customs' => [
//            'driver' => 'daily',
//            'path' => storage_path('logs/customs.log'),
//            'level' => 'debug',
//            'days' => 14,
//        ],
        'custom' => [
            'driver' => 'custom',
            'via' => App\Logging\CustomLogger::class,
            'level' => 'debug',
            'permission' => 0644,
        ],
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'database'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
        ],
        'database' => [
            'driver' => 'custom',
            'via' => App\Logging\DatabaseLogger::class,
        ],
    ],

];
