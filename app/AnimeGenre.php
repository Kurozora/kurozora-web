<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AnimeGenre extends Model
{
    // Table name
    const TABLE_NAME = 'anime_genre';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['anime_id', 'genre_id'];
}
