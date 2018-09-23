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
     * Check whether or not the user has an avatar set
     *
     * @return bool
     */
    public function hasAvatar() {
        return ($this->avatar != null);
    }

    /**
     * Returns the path to this user's avatar
     *
     * @return null|string
     */
    public function getAvatarPath() {
        if(!$this->hasAvatar()) return null;
        else return self::USER_UPLOADS_PATH . '/' . $this->avatar;
    }

    /**
     * Returns the URL to this user's avatar
     *
     * @return null|string
     */
    public function getURLToAvatar() {
        if(!$this->hasAvatar()) return null;
        else return env('APP_URL') . '/' . self::USER_UPLOADS_URL . '/' . $this->avatar;
    }

    /**
     * Returns the absolute URL to the user's avatar
     */
    public function getAvatarURL() {
        // No avatar uploaded
        if(!$this->hasAvatar()) return null;

        // Check if the uploaded image is present
        $avatarPath = $this->getAvatarPath();

        if(Storage::exists($avatarPath))
            // Return the URL to the image
            return $this->getURLToAvatar();
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
