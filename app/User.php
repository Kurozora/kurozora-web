<?php

namespace App;

use App\Enums\UserActivityStatus;
use App\Helpers\OptionsBag;
use App\Jobs\FetchSessionLocation;
use App\Notifications\NewSession;
use App\Traits\KuroSearchTrait;
use App\Traits\VoteActionTrait;
use App\Traits\MediaLibraryExtensionTrait;
use Cog\Contracts\Love\Reacterable\Models\Reacterable as ReacterableContract;
use Cog\Laravel\Love\Reacterable\Models\Traits\Reacterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements ReacterableContract, HasMedia
{
    use Notifiable,
        Authorizable,
        KuroSearchTrait,
        Reacterable,
        VoteActionTrait,
        InteractsWithMedia,
        MediaLibraryExtensionTrait,
        LogsActivity,
        HasRoles;

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

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

    /**
     * The attributes that should be cast to dates.
     *
     * @var array
     */
    protected $dates = [
        'last_mal_import_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'username_change_available' => 'boolean',
    ];

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

    // User biography character limited
    const BIOGRAPHY_LIMIT = 250;

    /**
     * Returns the user's activity status based on their sessions.
     *
     * @return UserActivityStatus
     */
    public function getActivityStatus()
    {
        /** @var Session $session */
        $session = $this->sessions()
            ->orderBy('last_validated_at', 'desc')
            ->first();

        if($session === null)
            return UserActivityStatus::Offline();

        // Seen within the last 5 minutes
        if($session->last_validated_at >= now()->subMinutes(5)) {
            return UserActivityStatus::Online();
        }
        // Seen within the last 15 minutes
        else if($session->last_validated_at >= now()->subMinutes(15)) {
            return UserActivityStatus::SeenRecently();
        }

        return UserActivityStatus::Offline();
    }

    /**
     * Finds the user with the given SIWA details.
     * e.g. User::findSIWA($id, $email);
     *
     * @param Builder $query
     * @param string $siwaID
     * @param string $email
     * @return User|null
     */
    public function scopeFindSIWA($query, $siwaID, $email)
    {
        /** @var User $user */
        $user = $query->where('email', $email)
            ->where('siwa_id', $siwaID);

        return $user;
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile();

        $this->addMediaCollection('banner')
            ->singleFile();
    }

    /**
     * Returns the Anime that the user has added to their favorites.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function favoriteAnime()
    {
        return $this->belongsToMany(Anime::class, UserFavoriteAnime::class, 'user_id', 'anime_id');
    }

    /**
     * Returns the Anime that the user has added to their reminders.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function reminderAnime()
    {
        return $this->belongsToMany(Anime::class, UserReminderAnime::class, 'user_id', 'anime_id');
    }

    /**
     * Returns a boolean indicating whether the user has the given anime in their library.
     *
     * @param Anime $anime The anime to be searched for in the user's library.
     *
     * @return bool
     */
    function isTracking(Anime $anime)
    {
        return $this->library()->where('anime_id', $anime->id)->exists();
    }

    /**
     * Returns the Anime that the user is moderating.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function moderatingAnime()
    {
        return $this->belongsToMany(Anime::class, AnimeModerator::class, 'user_id', 'anime_id')
            ->withPivot('created_at');
    }

    /**
     * Returns the Anime items in the user's library.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function library()
    {
        return $this->belongsToMany(Anime::class, UserLibrary::class, 'user_id', 'anime_id')
            ->withPivot('status');
    }

    /**
     * Returns the watched Episode items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function watchedAnimeEpisodes()
    {
        return $this->belongsToMany(AnimeEpisode::class, UserWatchedEpisode::class, 'user_id', 'episode_id');
    }

    /**
     * Returns a boolean indicating whether the user has watched the given episode.
     *
     * @param AnimeEpisode $episode
     *
     * @return bool
     */
    function hasWatched(AnimeEpisode $episode)
    {
        return $this->watchedAnimeEpisodes()->where('episode_id', $episode->id)->exists();
    }

    /**
     * Returns the associated badges for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    function badges()
    {
        return $this->belongsToMany(Badge::class, UserBadge::class, 'user_id', 'badge_id');
    }

    /**
     * Returns the associated sessions for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function sessions()
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Creates a new session for the user.
     *
     * Available options:
     * - `ip`: the IP address used to create the session, request IP is used by default.
     * - `platform`: the user's platform, null by default.
     * - `platform_version`: version of the user's platform, null by default.
     * - `device_vendor`: vendor of the user's device, null by default.
     * - `device_model`: model of the user's device, null by default.
     * - `retrieve_location`: should a job be dispatched to fetch location details, true by default.
     * - `notify`: should the user be notified of the new session, true by default.
     *
     * @param array $options
     * @return Session
     */
    function createSession($options = [])
    {
        $options = new OptionsBag($options);

        // Determine the IP address to use
        $ip = $options->get('ip');

        if($ip === null) $ip = request()->ip();

        /** @var Session $session */
        $session = $this->sessions()->create([
            'user_id'           => $this->id,
            'secret'            => Str::random(128),
            'expires_at'        => now()->addDays(Session::VALID_FOR_DAYS),
            'last_validated_at' => now(),
            'ip'                => $ip,

            // Platform information
            'platform'          => $options->get('platform'),
            'platform_version'  => $options->get('platform_version'),
            'device_vendor'     => $options->get('device_vendor'),
            'device_model'      => $options->get('device_model'),

        ]);

        // Dispatch job to retrieve location
        if($options->get('retrieve_location', true)) {
            dispatch(new FetchSessionLocation($session));
        }

        // Send notification
        if($options->get('notify', true)) {
            $this->notify(new NewSession($session->ip, $session));
        }

        return $session;
    }

    /**
     * Returns the associated threads for the user
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    function threads()
    {
        return $this->hasMany(ForumThread::class);
    }

    /**
     * Returns a list of badges that the user has assigned to them
     *
     * @return array
     */
    public function getBadges()
    {
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'following_user_id', 'user_id');
    }

    /**
     * Returns the amount of followers the user has
     *
     * @return int
     */
    public function getFollowerCount()
    {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWER_COUNT, $this->id);

        // Retrieve or save cached result
        $followerCount = Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWER_COUNT_SECONDS, function () {
            return $this->followers()->count();
        });

        return $followerCount;
    }

    /**
     * Get the user's following users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function following()
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'user_id', 'following_user_id');
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
    public function getReputationCount()
    {
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
    public static function hashPass($rawPass)
    {
        return Hash::make($rawPass);
    }

    /**
     * Compares a raw password with a password hash
     *
     * @param $rawPass
     * @param $hash
     * @return bool
     */
    public static function checkPassHash($rawPass, $hash)
    {
        return Hash::check($rawPass, $hash);
    }

    /**
     * Checks if this user has confirmed their email address
     *
     * @return bool
     */
    public function hasConfirmedEmail()
    {
        return ($this->email_confirmation_id == null);
    }

    /**
     * Checks the cooldown whether the user can do a MAL import.
     *
     * @return bool
     */
    function canDoMALImport()
    {
        if(!$this->last_mal_import_at)
            return true;

        if($this->last_mal_import_at > now()->subDays(config('mal-import.cooldown_in_days')))
            return false;

        return true;
    }

    /**
     * Returns the APN token(s) for the user.
     *
     * @return string|array
     */
    public function routeNotificationForApn()
    {
        return $this->sessions()
            ->whereNotNull('apn_device_token')
            ->pluck('apn_device_token')
            ->toArray();
    }
}
