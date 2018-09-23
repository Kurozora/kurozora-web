<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class User extends Model
{
    // Path where user uploads are stored
    const USER_UPLOADS_PATH = 'public/img/user_uploads';
    const USER_UPLOADS_URL  = 'img/user_uploads';

    // User roles
    const USER_ROLE_NORMAL          = 0;
    const USER_ROLE_MODERATOR       = 1;
    const USER_ROLE_ADMINISTRATOR   = 2;

    protected $fillable = ['username', 'email', 'password', 'email_confirmation_id', 'avatar'];

    /**
     * Checks if this user has confirmed their email address
     *
     * @return bool
     */
    public function hasConfirmedEmail() {
        return ($this->email_confirmation_id == null);
    }

    /**
     * Returns the absolute URL to the user's avatar
     */
    public function getAvatarURL() {
        // No avatar uploaded
        if($this->avatar == null) return null;

        // Check if the uploaded image is present
        $avatarPath = self::USER_UPLOADS_PATH . '/' . $this->avatar;

        if(Storage::exists($avatarPath))
            // Return the URL to the image
            return env('APP_URL') . '/' . self::USER_UPLOADS_URL . '/' . $this->avatar;
        else return null;
    }

    /**
     * Checks if a user can authenticate with the given details
     *
     * @param $userID
     * @param $sessionSecret
     * @return bool
     */
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
