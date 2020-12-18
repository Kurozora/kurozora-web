<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserFavoriteAnime extends Model
{
    // Table name
    const TABLE_NAME = 'user_favorite_animes';
    protected $table = self::TABLE_NAME;
}
