<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    // Table name
    const TABLE_NAME = 'user_follow';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['user_id', 'following_user_id'];
}
