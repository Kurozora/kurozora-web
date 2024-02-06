<?php

namespace App\Models;

class UserReputation extends KModel
{
    // Table name
    const string TABLE_NAME = 'user_reputations';
    protected $table = self::TABLE_NAME;
}
