<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Social Networks
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for social networks such
    | as Twitter, Instagram, Discord and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various social networks.
    |
    */

    'twitter' => [
        'url' => 'https://twitter.com/' .  env('TWITTER_HANDLE'),
        'username' => env('TWITTER_HANDLE', 'KurozoraApp'),
    ],

    'instagram' => [
        'url' => 'https://instagram.com/' . env('INSTAGRAM_HANDLE'),
        'username' => env('INSTAGRAM_HANDLE', 'kurozora_app'),
    ],

    'discord' => [
        'url' => 'https://discord.gg/' . env('DISCORD_INVITE'),
        'username' => env('DISCORD_INVITE', 'f3QFzGqsah'),
    ],

    'reddit' => [
        'url' => 'https://www.reddit.com/r/' . env('REDDIT_HANDLE'),
        'username' => env('REDDIT_HANDLE', 'Kurozora'),
    ]

];
