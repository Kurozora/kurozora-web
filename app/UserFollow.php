<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    // Table name
    protected $table = 'user_follow';

    // Fillable columns
    protected $fillable = ['user_id', 'following_user_id', 'notifications'];
}
