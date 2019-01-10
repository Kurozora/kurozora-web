<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AnimeEpisode extends Model
{
    // Table name
    const TABLE_NAME = 'anime_episode';
    protected $table = self::TABLE_NAME;

    // Make all columns fillable
    protected $guarded = [];
}
