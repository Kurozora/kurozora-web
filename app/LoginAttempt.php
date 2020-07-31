<?php

namespace App;

class LoginAttempt extends KModel
{
    // How far back should we check for login attempts (minutes)
    const CHECK_TIMESPAN = 5;

    // How many failed attempts do we tolerate in the timespan
    const MAX_FAILED_ATTEMPTS = 3;

    // Table name
    const TABLE_NAME = 'login_attempts';
    protected $table = self::TABLE_NAME;

    /**
     * Checks if the given IP is allowed to login (in terms of login requests)
     *
     * @param string $ip
     * @return bool
     */
    public static function isIPAllowedToLogin($ip) {
        $attemptCount = LoginAttempt::where([
            ['ip', '=', $ip],
            ['created_at', '>=', now()->subMinutes(self::CHECK_TIMESPAN)]
        ])->count();

        return ($attemptCount < self::MAX_FAILED_ATTEMPTS);
    }

    /**
     * Registers a failed login attempt for an IP address
     *
     * @param string $ip
     */
    public static function registerFailedLoginAttempt($ip) {
        LoginAttempt::create(['ip'=> $ip]);
    }
}
