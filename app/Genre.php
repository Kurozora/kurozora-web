<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Genre extends Model
{
    // Table name
    const TABLE_NAME = 'genre';
    protected $table = self::TABLE_NAME;
}
