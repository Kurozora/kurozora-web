<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoginAttempt extends Model
{
	// How far back should we check for login attempts (minutes)
	const CHECK_TIMESPAN = 5;

	// How many failed attempts do we tolerate in the timespan
	const MAX_FAILED_ATTEMPTS = 3;

    // Table name
    protected $table = 'user_login_attempt';

	// Fillable columns
    protected $fillable = ['ip'];

	/**
     * Checks if the given IP is allowed to login (in terms of login requests)
     *
     * @param string $ip
     * @return bool
     */
    public static function isIPAllowedToLogin($ip) {
    	$minimumUnixTime = time() - (self::CHECK_TIMESPAN * 60);

    	$attemptCount = LoginAttempt::where([
    		['ip', '=', $ip],
    		['created_at', '>=', date('Y-m-d H:i:s', $minimumUnixTime)]
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
