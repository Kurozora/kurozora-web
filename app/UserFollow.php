<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserFollow extends Model
{
    // Fillable columns
    protected $fillable = ['user_id', 'following_user_id', 'notifications'];
}
