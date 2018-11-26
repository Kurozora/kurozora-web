<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserReputation extends Model
{
    // Table name
    const TABLE_NAME = 'user_reputation';
    protected $table = self::TABLE_NAME;
}
