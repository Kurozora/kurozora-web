<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    // The length of a reset token
    const TOKEN_LENGTH = 30;

    // For how many hours is the password reset valid
    const VALID_HOURS = 24;

    // Table name
    const TABLE_NAME = 'user_password_reset';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['user_id', 'ip', 'token'];

    /**
     * Returns a token to use with password reset links
     *
     * @return string
     */
    public static function genToken()
    {
        return str_random(self::TOKEN_LENGTH);
    }

    /**
     * Generates a new temporary password
     *
     * @return string
     */
    public static function genTempPassword() {
        return str_random(10);
    }
}
