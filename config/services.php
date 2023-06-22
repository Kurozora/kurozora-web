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
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
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

    'patreon' => [
        'url' => 'https://patreon.com/' . env('PATREON_HANDLE', 'kurozora'),
        'username' => env('PATREON_HANDLE', 'kurozora'),
    ],

    'paypal' => [
        'url' => 'https://paypal.com/paypalme/' . env('PAYPAL_HANDLE', 'Kiritokatklian'),
        'username' => env('PAYPAL_HANDLE', 'Kiritokatklian'),
    ],

    'spotify' => [
        'url' => 'https://spotify.com/',
        'track' => 'https://open.spotify.com/track/',
    ],

    'tidal' => [
        'url' => 'https://tidal.com/',
        'track' => 'https://tidal.com/browse/track/',
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
