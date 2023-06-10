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

    'discord' => [
        'url' => 'https://discord.gg/' . env('DISCORD_INVITE', 'f3QFzGqsah'),
        'username' => env('DISCORD_HANDLE', 'Kurozora'),
    ],

    'github' => [
        'url' => 'https://github.com/' . env('GITHUB_HANDLE', 'Kurozora'),
        'username' => env('GITHUB_HANDLE', 'Kurozora'),
    ],

    'instagram' => [
        'url' => 'https://instagram.com/' . env('INSTAGRAM_HANDLE', 'Kurozora_App'),
        'username' => env('INSTAGRAM_HANDLE', 'Kurozora_App'),
    ],

    'mastodon' => [
        'url' => 'https://mastodon.social/' . env('MASTODON_HANDLE', '@Kurozora'),
        'username' => env('MASTODON_HANDLE', '@Kurozora'),
    ],

    'reddit' => [
        'url' => 'https://www.reddit.com/r/' . env('REDDIT_HANDLE', 'Kurozora'),
        'username' => env('REDDIT_HANDLE', 'Kurozora'),
    ],

    'twitter' => [
        'url' => 'https://twitter.com/' . env('TWITTER_HANDLE', 'KurozoraApp'),
        'username' => env('TWITTER_HANDLE', 'KurozoraApp'),
    ],

    'youtube' => [
        'url' => 'https://www.youtube.com/' . env('YOUTUBE_HANDLE', '@KurozoraApp'),
        'username' => env('YOUTUBE_HANDLE', '@KurozoraApp'),
    ]

];
