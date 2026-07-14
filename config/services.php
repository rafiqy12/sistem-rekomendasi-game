<?php

return [

    'game_covers' => [
        'enabled' => env('GAME_COVERS_ENABLED', true),
        'url' => env('GAME_COVERS_URL', 'https://www.cheapshark.com/api/1.0/games'),
        'timeout' => env('GAME_COVERS_TIMEOUT', 3),
        'user_agent' => env('GAME_COVERS_USER_AGENT', 'SistemRekomendasiGame/1.0 (educational Laravel project)'),
        'verify_ssl' => env('GAME_COVERS_VERIFY_SSL', true),
    ],

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

];
