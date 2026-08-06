<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Scrapeable Domains
    |--------------------------------------------------------------------------
    |
    | This file is for storing the scrapeable domains such as MAL and TVDB,
    | and others. This file provides a sane default location for this type
    | of information, allowing packages to have a conventional place to
    | find your various domains.
    |
    */

    'domains' => [
        'mal' => [
            'base' => 'https://myanimelist.net',
            'anime' => 'https://myanimelist.net/anime',
            'anime_characters' => 'https://myanimelist.net/anime/:x/MAL/characters',
            'anime_season' => 'https://myanimelist.net/anime/season',
            'anime_stats' => 'https://myanimelist.net/anime/:x/MAL/stats',
            'top_anime' => 'https://myanimelist.net/topanime.php',
            'upcoming_anime' => 'https://myanimelist.net/anime.php?o=9',
            'character' => 'https://myanimelist.net/character',
            'company' => 'https://myanimelist.net/company',
            'producer' => 'https://myanimelist.net/anime/producer',
            'magazine' => 'https://myanimelist.net/manga/magazine',
            'manga' => 'https://myanimelist.net/manga',
            'top_manga' => 'https://myanimelist.net/topmanga.php',
            'upcoming_manga' => 'https://myanimelist.net/manga.php?o=9',
            'people' => 'https://myanimelist.net/people',
            'animelist' => [
                'base' => 'https://myanimelist.net/animelist/:x',
                'json' => 'https://myanimelist.net/animelist/:x/load.json',
            ],
            'mangalist' => [
                'base' => 'https://myanimelist.net/mangalist/:x',
                'json' => 'https://myanimelist.net/mangalist/:x/load.json',
            ],
        ],

        'tvdb' => [
            'base' => 'https://thetvdb.com',
            'dereferrer' => [
                'series' => 'https://thetvdb.com/dereferrer/series'
            ],
            'tab' => [
                'series' => 'https://thetvdb.com/?tab=series&id='
            ]
        ],

        'anime_filler_list' => [
            'base' => 'https://www.animefillerlist.com',
            'shows' => 'https://www.animefillerlist.com/shows',
        ],

        'igdb' => [
            'base' => 'https://www.igdb.com',
            'gql' => 'https://www.igdb.com/gql',
            'autocomplete' => 'https://www.igdb.com/search_autocomplete_all',
            'category' => 'https://www.igdb.com/categories/:x',
            'genre' => 'https://www.igdb.com/genres/:x',
            'game' => 'https://www.igdb.com/games/:x',
            'image' => 'https://images.igdb.com/igdb/image/upload/t_1080p/:x.jpg',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Shared rate-limit buckets applied across every spider run that targets
    | the same upstream. Keeps cross-run amplification bounded.
    |
    */

    'rate_limits' => [
        'igdb' => [
            'key' => 'scraper:igdb',
            'max_attempts' => env('SCRAPER_IGDB_MAX_ATTEMPTS', 20),
            'decay_seconds' => env('SCRAPER_IGDB_DECAY_SECONDS', 60),
            'max_wait_seconds' => env('SCRAPER_IGDB_MAX_WAIT_SECONDS', 300),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | curl-impersonate
    |--------------------------------------------------------------------------
    |
    | IGDB's game pages sit behind a Cloudflare fingerprint challenge that PHP's
    | native TLS stack cannot pass. The IGDB spider shells out to curl-impersonate
    | so requests carry a genuine browser TLS/HTTP2 fingerprint.
    |
    */

    'curl_impersonate' => [
        'binary' => env('CURL_IMPERSONATE_BINARY', 'curl-impersonate'),
        'profile' => env('CURL_IMPERSONATE_PROFILE', 'chrome131'),
        'timeout' => env('CURL_IMPERSONATE_TIMEOUT', 30),
    ],

];
