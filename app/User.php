<?php

namespace App;

use App\Traits\KuroSearchTrait;
use App\Traits\LikeActionTrait;
use Cog\Contracts\Love\Liker\Models\Liker as LikerContract;
use Cog\Laravel\Love\Liker\Models\Traits\Liker;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property mixed id
 * @property array|string|null biography
 * @property string avatar
 */
class User extends Authenticatable implements LikerContract
{
    use Authorizable, KuroSearchTrait, Liker, LikeActionTrait;

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

    // Cache user's badges
    const CACHE_KEY_BADGES = 'user-badges-%d';
    const CACHE_KEY_BADGES_SECONDS = 120 * 60;

    // Cache user's follower count
    const CACHE_KEY_FOLLOWER_COUNT = 'user-followers-%d';
    const CACHE_KEY_FOLLOWER_COUNT_SECONDS = 10 * 60;

    // Cache user's following count
    const CACHE_KEY_FOLLOWING_COUNT = 'user-following-%d';
    const CACHE_KEY_FOLLOWING_COUNT_SECONDS = 10 * 60;

    // Cache user's reputation count
    const CACHE_KEY_REPUTATION_COUNT = 'user-reputation-%d';
    const CACHE_KEY_REPUTATION_COUNT_SECONDS = 10 * 60;

    // Table name
    const TABLE_NAME = 'users';
    protected $table = self::TABLE_NAME;

    // Remove column guards
    protected $guarded = [];

    // User biography character limited
    const BIOGRAPHY_LIMIT = 250;

    /**
     * Returns the associated badges for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function badges() {
        return $this->belongsToMany(Badge::class, UserBadge::class, 'user_id', 'badge_id');
    }

    /**
     * Returns the associated sessions for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function sessions() {
        return $this->hasMany(Session::class);
    }

    /**
     * Returns the associated threads for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function threads() {
        return $this->hasMany(ForumThread::class);
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
        $badgeInfo = Cache::remember($cacheKey, self::CACHE_KEY_BADGES_SECONDS, function () {
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
     * Get the user's followers
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function followers() {
        return $this->hasMany(UserFollow::class, 'following_user_id');
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
        $followerCount = Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWER_COUNT_SECONDS, function () {
            return $this->followers()->count();
        });

        return $followerCount;
    }

    /**
     * Get the user's following users
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function following() {
        return $this->hasMany(UserFollow::class, 'user_id');
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
        $followingCount = Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWING_COUNT_SECONDS, function () {
            return $this->following()->count();
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
        $repCount = Cache::remember($cacheKey, self::CACHE_KEY_REPUTATION_COUNT_SECONDS, function () {
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
