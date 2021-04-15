<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasswordReset extends KModel
{
    use HasFactory;

    // Table name
    const TABLE_NAME = 'password_resets';
    protected $table = self::TABLE_NAME;
}
