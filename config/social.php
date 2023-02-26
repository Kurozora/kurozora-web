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
        'url' => 'https://twitter.com/' . env('TWITTER_HANDLE', 'KurozoraApp'),
        'username' => env('TWITTER_HANDLE', 'KurozoraApp'),
    ],

    'instagram' => [
        'url' => 'https://instagram.com/' . env('INSTAGRAM_HANDLE', 'kurozora_app'),
        'username' => env('INSTAGRAM_HANDLE', 'kurozora_app'),
    ],

    'discord' => [
        'url' => 'https://discord.gg/' . env('DISCORD_INVITE', 'f3QFzGqsah'),
        'username' => env('DISCORD_HANDLE', 'Kurozora'),
    ],

    'reddit' => [
        'url' => 'https://www.reddit.com/r/' . env('REDDIT_HANDLE', 'Kurozora'),
        'username' => env('REDDIT_HANDLE', 'Kurozora'),
    ],

    'youtube' => [
        'url' => 'https://www.youtube.com/' . env('YOUTUBE_HANDLE', '@Kurozora'),
        'username' => env('YOUTUBE_HANDLE', '@Kurozora'),
    ]

];
