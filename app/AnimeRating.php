<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeRating extends Model
{
    // Rating boundries
    const MIN_RATING_VALUE = 0.00;
    const MAX_RATING_VALUE = 5.00;

    // Table name
    const TABLE_NAME = 'anime_rating';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = [
        'anime_id',
        'user_id',
        'rating'
    ];
}
