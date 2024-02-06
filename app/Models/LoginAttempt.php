<?php

namespace App\Models;

class LoginAttempt extends KModel
{
    // How far back should we check for sign in attempts (minutes)
    const int CHECK_TIMESPAN = 5;

    // How many failed attempts do we tolerate in the timespan
    const int MAX_FAILED_ATTEMPTS = 3;

    // Table name
    const string TABLE_NAME = 'login_attempts';
    protected $table = self::TABLE_NAME;

    /**
     * Checks if the given IP is allowed to sign in (in terms of sign in requests)
     *
     * @param string $ipAddress
     * @return bool
     */
    public static function isIPAllowedToLogin(string $ipAddress): bool
    {
        $attemptCount = LoginAttempt::where([
            ['ip_address', '=', $ipAddress],
            ['created_at', '>=', now()->subMinutes(self::CHECK_TIMESPAN)]
        ])->count();

        return ($attemptCount < self::MAX_FAILED_ATTEMPTS);
    }

    /**
     * Registers a failed sign in attempt for an IP address
     *
     * @param string $ipAddress
     */
    public static function registerFailedLoginAttempt(string $ipAddress)
    {
        LoginAttempt::create(['ip_address' => $ipAddress]);
    }
}
