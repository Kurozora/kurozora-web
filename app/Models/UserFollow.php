<?php

namespace App\Models;

class UserFollow extends KModel
{
    // Table name
    const TABLE_NAME = 'user_follows';
    protected $table = self::TABLE_NAME;
}
