<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'waclient' => [
        'access_token' => env('WACLIENT_ACCESS_TOKEN'),
        'instance_id' => env('WACLIENT_INSTANCE_ID'),
        'timeout' => env('WACLIENT_TIMEOUT', 30),
        'log_success' => env('WACLIENT_LOG_SUCCESS', true),
        'rate_limit_per_minute' => env('WACLIENT_RATE_LIMIT_PER_MINUTE', 20),
        'rate_limit_per_hour' => env('WACLIENT_RATE_LIMIT_PER_HOUR', 300),
    ],

];
