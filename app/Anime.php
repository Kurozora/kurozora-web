<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Anime extends Model
{
    protected $fillable = [
        'title',
        'cached_poster',
        'cached_poster_thumbnail',
        'cached_background',
        'cached_background_thumbnail',
        'type',
        'nsfw',
        'tvdb_id'
    ];

    const ANIME_TYPE_UNDEFINED  = 0;
    const ANIME_TYPE_TV         = 1;
    const ANIME_TYPE_MOVIE      = 2;
    const ANIME_TYPE_SPECIAL    = 3;
    const ANIME_TYPE_OVA        = 4;
    const ANIME_TYPE_ONA        = 5;
}
