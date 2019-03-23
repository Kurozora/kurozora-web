<?php

namespace App;

use Illuminate\Support\Str;

class PasswordReset extends KModel
{
    // The length of a reset token
    const TOKEN_LENGTH = 30;

    // For how many hours is the password reset valid
    const VALID_HOURS = 24;

    // Table name
    const TABLE_NAME = 'user_password_reset';
    protected $table = self::TABLE_NAME;

    /**
     * Returns a token to use with password reset links
     *
     * @return string
     */
    public static function genToken()
    {
        return Str::random(self::TOKEN_LENGTH);
    }

    /**
     * Generates a new temporary password
     *
     * @return string
     */
    public static function genTempPassword() {
        return Str::random(10);
    }
}
