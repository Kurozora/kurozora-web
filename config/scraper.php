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
        ],
        'tvdb' => [
            'base' => 'https://tvdb.com'
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
