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
            'anime_season' => 'https://myanimelist.net/anime/season',
            'top_anime' => 'https://myanimelist.net/topanime.php',
            'upcoming_anime' => 'https://myanimelist.net/anime.php?o=9',
            'manga' => 'https://myanimelist.net/manga',
            'top_manga' => 'https://myanimelist.net/topmanga.php',
            'upcoming_manga' => 'https://myanimelist.net/manga.php?o=9',
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
    ]

];
