<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserFollow extends Pivot
{
    // Table name
    const string TABLE_NAME = 'user_follows';
    protected $table = self::TABLE_NAME;
}
