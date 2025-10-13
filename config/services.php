<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key'    => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe'    => [
        'model'  => App\Models\User::class,
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],
    'portmanat' => [
        'partner_id' => '16510',
        'service_id' => '714',
        'key'        => 'ASE@2019!',
    ],
    'kapital_bank' => [
        'base_url' => env('KAPITAL_BANK_BASE_URL'),
        'username' => env('KAPITAL_BANK_USERNAME'),
        'password' => env('KAPITAL_BANK_PASSWORD'),
        'hpp_url' => env('KAPITAL_BANK_HPP_URL', 'https://txpgtst.kapitalbank.az/flex'),
    ]

];
