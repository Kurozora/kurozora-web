<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    // Table name
    const TABLE_NAME = 'anime_episode';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = [
        'season_id',
        'number',
        'name',
        'first_aired',
        'overview'
    ];
}
