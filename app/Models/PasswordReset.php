<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasswordReset extends KModel
{
    use HasFactory;

    // Table name
    const string TABLE_NAME = 'password_reset_tokens';
    protected $table = self::TABLE_NAME;
}
