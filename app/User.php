<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    // Path where user uploads are stored
    const USER_UPLOADS_PATH = 'public/img/user_uploads';

    // User roles
    const USER_ROLE_NORMAL          = 0;
    const USER_ROLE_MODERATOR       = 1;
    const USER_ROLE_ADMINISTRATOR   = 2;

    protected $fillable = ['username', 'email', 'password', 'email_confirmation_id', 'avatar'];

    // Checks if this user has confirmed their email address
    public function hasConfirmedEmail() {
        return ($this->email_confirmation_id == null);
    }

    // Checks if a user can authenticate with the given details
    public static function authenticateSession($userID, $sessionSecret) {
        // Find the session
        $foundSession = Session::where([
            ['user_id', '=', $userID],
            ['secret',  '=', $sessionSecret]
        ])->first();

        // Session not found
        if($foundSession == null)
            return false;

        // Check if it's expired
        if($foundSession->isExpired()) {
            $foundSession->delete();
            return false;
        }

        // All valid
        return true;
    }
}
