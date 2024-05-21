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

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'amazon' => [
        'music' => [
            'base' => 'https://amazon.com/music/player/',
            'albums' => 'https://amazon.com/music/player/albums/',
        ]
    ],

    'apple' => [
        'store_kit' => [
            'password' => env('SK_APP_PASSWORD'),
            'issuer_id' => env('SK_ISSUER_ID'),
        ],
        'client_id' => env('APPLE_CLIENT_ID', 'app.kurozora.web.tracker'),
        'client_secret' => env('MIX_APPLE_CLIENT_SECRET'),
        'redirect' => env('APPLE_REDIRECT_URI', '/siwa/callback'),
    ],

    'deezer' => [
        'url' => 'https://deezer.com/',
        'track' => 'https://deezer.com/track/',
    ],

    'ko-fi' => [
        'url' => 'https://ko-fi.com/' . env('KOFI_HANDLE', 'kurozora'),
        'username' => env('KOFI_HANDLE', 'kurozora'),
    ],

    'open_collective' => [
        'url' => 'https://opencollective.com/' . env('OPEN_COLLECTIVE_HANDLE', 'kurozora'),
        'username' => env('OPEN_COLLECTIVE_HANDLE', 'kurozora'),
    ],

    'patreon' => [
        'url' => 'https://patreon.com/' . env('PATREON_HANDLE', 'kurozora'),
        'username' => env('PATREON_HANDLE', 'kurozora'),
    ],

    'paypal' => [
        'url' => 'https://paypal.com/paypalme/' . env('PAYPAL_HANDLE', 'Kiritokatklian'),
        'username' => env('PAYPAL_HANDLE', 'Kiritokatklian'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'spotify' => [
        'url' => 'https://spotify.com/',
        'track' => 'https://open.spotify.com/track/',
    ],

    'youtube' => [
        'url' => 'https://youtube.com/',
        'music' => [
            'base' => 'https://music.youtube.com/',
            'watch' => 'https://music.youtube.com/watch?v='
        ],
        'api_key' => env('YOUTUBE_API_KEY'),
    ],

];
