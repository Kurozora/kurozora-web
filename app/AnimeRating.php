<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeRating extends Model
{
    // Rating boundries
    const MIN_RATING_VALUE = 0.00;
    const MAX_RATING_VALUE = 5.00;

    protected $table = 'anime_ratings';

    protected $fillable = [
        'anime_id',
        'user_id',
        'rating'
    ];
}
