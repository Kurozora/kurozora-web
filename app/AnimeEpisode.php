<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    protected $fillable = [
        'anime_id',
        'season',
        'number',
        'name',
        'first_aired',
        'overview'
    ];
}
