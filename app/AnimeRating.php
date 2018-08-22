<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeRating extends Model
{
    protected $table = 'anime_ratings';

    protected $fillable = [
        'anime_id',
        'user_id',
        'rating'
    ];
}
