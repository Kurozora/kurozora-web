<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UserLibrary extends Pivot
{
    // Table name
    const TABLE_NAME = 'user_libraries';
    protected $table = self::TABLE_NAME;
}
