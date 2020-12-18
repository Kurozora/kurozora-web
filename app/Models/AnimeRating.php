<?php

namespace App\Models;

class AnimeRating extends KModel
{
    // Rating boundaries
    const MIN_RATING_VALUE = 0.00;
    const MAX_RATING_VALUE = 5.00;

    // Table name
    const TABLE_NAME = 'anime_ratings';
    protected $table = self::TABLE_NAME;
}
