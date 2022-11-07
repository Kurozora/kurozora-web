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
            'upcoming_anime' => 'https://myanimelist.net/anime.php?o=9',
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
        'animix_play' => [
            'base' => 'https://animixplay.to',
            'api' => 'https://animixplay.to/v1',
        ],
        'anime_filler_list' => [
            'base' => 'https://www.animefillerlist.com',
            'shows' => 'https://www.animefillerlist.com/shows',
        ],
    ]

];
