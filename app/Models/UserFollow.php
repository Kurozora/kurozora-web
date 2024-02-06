<?php

namespace App\Models;

class UserFollow extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_follows';
    protected $table = self::TABLE_NAME;
}
