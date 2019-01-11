<?php

namespace App;

use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;
use KuroSearchTrait;

class User extends Authenticatable
{
    use Authorizable, KuroSearchTrait;

    /**
     * Searchable rules.
     *
     * @var array
     */
    protected $searchable = [
        'columns' => [
            'username' => 10
        ]
    ];

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Path where user uploads are stored
    const USER_UPLOADS_PATH = 'public/img/user_uploads';
    const USER_UPLOADS_URL  = 'img/user_uploads';

    // User roles
    const USER_ROLE_NORMAL          = 0;
    const USER_ROLE_MODERATOR       = 1;
    const USER_ROLE_ADMINISTRATOR   = 2;

    // Map user roles to string
    const ROLE_MAPPING = [
        self::USER_ROLE_NORMAL          => 'normal',
        self::USER_ROLE_MODERATOR       => 'moderator',
        self::USER_ROLE_ADMINISTRATOR   => 'administrator'
    ];

    // Cache user's badges
    const CACHE_KEY_BADGES = 'user-badges-%d';
    const CACHE_KEY_BADGES_MINUTES = 120;

    // Cache user's follower count
    const CACHE_KEY_FOLLOWER_COUNT = 'user-followers-%d';
    const CACHE_KEY_FOLLOWER_COUNT_MINUTES = 10;

    // Cache user's following count
    const CACHE_KEY_FOLLOWING_COUNT = 'user-following-%d';
    const CACHE_KEY_FOLLOWING_COUNT_MINUTES = 10;

    // Cache user's reputation count
    const CACHE_KEY_REPUTATION_COUNT = 'user-reputation-%d';
    const CACHE_KEY_REPUTATION_COUNT_MINUTES = 10;

    // Table name
    const TABLE_NAME = 'user';
    protected $table = self::TABLE_NAME;

    // Fillable columns
    protected $fillable = ['username', 'email', 'password', 'email_confirmation_id', 'avatar'];

    // User biography character limited
    const BIOGRAPHY_LIMIT = 250;

    /**
     * Returns the name of a role from the integer constant
     *
     * @param $roleInt
     * @return string|null
     */
    public static function getStringFromRole($roleInt) {
        // Find the status
        foreach(self::ROLE_MAPPING as $role => $string) {
            if($roleInt == $role)
                return $string;
        }

        return null;
    }

    /**
     * Returns a list of badges that the user has assigned to them
     *
     * @return array
     */
    public function getBadges() {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_BADGES, $this->id);

        // Retrieve or save cached result
        $badgeInfo = Cache::remember($cacheKey, self::CACHE_KEY_BADGES_MINUTES, function () {
            return Badge::
                join(UserBadge::TABLE_NAME, function ($join) {
                    $join->on(UserBadge::TABLE_NAME . '.badge_id', '=', Badge::TABLE_NAME . '.id');
                })
                ->where([
                    [UserBadge::TABLE_NAME . '.user_id', '=', $this->id]
                ])
                ->get();
        });

        return $badgeInfo;
    }

    /**
     * Returns the amount of followers the user has
     *
     * @return int
     */
    public function getFollowerCount() {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWER_COUNT, $this->id);

        // Retrieve or save cached result
        $followerCount = Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWER_COUNT_MINUTES, function () {
            return UserFollow::where('following_user_id', $this->id)->count();
        });

        return $followerCount;
    }

    /**
     * Returns the amount of users the user follows
     *
     * @return int
     */
    public function getFollowingCount() {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWING_COUNT, $this->id);

        // Retrieve or save cached result
        $followingCount = Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWING_COUNT_MINUTES, function () {
            return UserFollow::where('user_id', $this->id)->count();
        });

        return $followingCount;
    }

    /**
     * Returns the total amount of reputation the user has
     *
     * @return int
     */
    public function getReputationCount() {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_REPUTATION_COUNT, $this->id);

        // Retrieve or save cached result
        $repCount = Cache::remember($cacheKey, self::CACHE_KEY_REPUTATION_COUNT_MINUTES, function () {
            $foundRep = UserReputation::where('given_user_id', $this->id)->sum('amount');

            if($foundRep === null) return 0;
            return (int) $foundRep;
        });

        return (int) $repCount;
    }

    /**
     * Generates a password hash from a raw string
     *
     * @param $rawPass
     * @return string
     */
    public static function hashPass($rawPass) {
        return Hash::make($rawPass);
    }

    /**
     * Compares a raw password with a password hash
     *
     * @param $rawPass
     * @param $hash
     * @return bool
     */
    public static function checkPassHash($rawPass, $hash) {
        return Hash::check($rawPass, $hash);
    }

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
        else return url('/' . self::USER_UPLOADS_URL . '/' . $this->avatar);
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
     * Takes a avatar filename and returns the URL to it
     *
     * @param $fileName
     * @return string
     */
    public static function avatarFileToURL($fileName) {
        if($fileName === null)
            return null;

        $filePath = self::USER_UPLOADS_PATH . '/' . $fileName;

        if(Storage::exists($filePath))
            // Return the URL to the image
            return url($filePath);
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
        // Parse to int
        $userID = (int) $userID;

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
        return $foundSession->id;
    }

    /**
     * Checks whether the user is following another us
     *
     * @param User $user
     * @return bool
     */
    public function isFollowing(User $user) {
        return UserFollow::where('user_id', $this->id)
            ->where('following_user_id', $user->id)
            ->exists();
    }


}
