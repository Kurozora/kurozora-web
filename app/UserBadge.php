<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBadge extends Model
{
    // Table name
    const TABLE_NAME = 'user_badge';
    protected $table = self::TABLE_NAME;
}
