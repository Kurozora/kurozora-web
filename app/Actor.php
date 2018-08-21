<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Actor extends Model
{
    protected $table = 'anime_actors';

    protected $fillable = [
        'anime_id',
        'name',
        'role',
        'image'
    ];
}
