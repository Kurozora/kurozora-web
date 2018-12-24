<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWatchedEpisode extends Model
{
    // Table name
    const TABLE_NAME = 'user_watched_episode';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['user_id', 'episode_id'];
}
