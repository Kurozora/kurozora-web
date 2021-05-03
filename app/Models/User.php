<?php

namespace App\Models;

use App\Enums\UserActivityStatus;
use App\Helpers\OptionsBag;
use App\Jobs\FetchSessionLocation;
use App\Notifications\NewSession;
use App\Traits\HeartActionTrait;
use App\Traits\KuroSearchTrait;
use App\Traits\MediaLibraryExtensionTrait;
use App\Traits\User\HasBannerImage;
use App\Traits\User\HasProfileImage;
use App\Traits\VoteActionTrait;
use App\Traits\Web\Auth\TwoFactorAuthenticatable;
use Carbon\Carbon;
use Cog\Contracts\Love\Reacterable\Models\Reacterable as ReacterableContract;
use Cog\Laravel\Love\Reacterable\Models\Traits\Reacterable;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\IcalendarGenerator\Components\Alert;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\Parameter;
use Spatie\IcalendarGenerator\Properties\TextProperty;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail, ReacterableContract
{
    use Authorizable,
        HasBannerImage,
        HasFactory,
        HasProfileImage,
        HasRoles,
        HeartActionTrait,
        InteractsWithMedia,
        KuroSearchTrait,
        LogsActivity,
        MediaLibraryExtensionTrait,
        Notifiable,
        Reacterable,
        TwoFactorAuthenticatable,
        VoteActionTrait;

    // Maximum amount of returned search results
    const MAX_SEARCH_RESULTS = 10;

    // Cache user's badges
    const CACHE_KEY_BADGES = 'user-badges-%d';
    const CACHE_KEY_BADGES_SECONDS = 120 * 60;

    // Cache user's calendar
    const CACHE_KEY_CALENDAR_SECONDS = 60 * 60 * 24;

    // Cache user's follower count
    const CACHE_KEY_FOLLOWER_COUNT = 'user-followers-%d';
    const CACHE_KEY_FOLLOWER_COUNT_SECONDS = 10 * 60;

    // Cache user's following count
    const CACHE_KEY_FOLLOWING_COUNT = 'user-following-%d';
    const CACHE_KEY_FOLLOWING_COUNT_SECONDS = 10 * 60;

    // Cache user's reputation count
    const CACHE_KEY_REPUTATION_COUNT = 'user-reputation-%d';
    const CACHE_KEY_REPUTATION_COUNT_SECONDS = 10 * 60;

    // User biography character limited
    const BIOGRAPHY_LIMIT = 250;

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
        'settings' => 'json'
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_image',
        'profile_image_url',
        'banner_image',
        'banner_image_url'
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

    /**
     * The name of the banner image media collection.
     *
     * @var string $bannerImageCollectionName
     */
    protected string $bannerImageCollectionName = 'banner';

    /**
     * The name of the profile image media collection.
     *
     * @var string $profileImageCollectionName
     */
    protected string $profileImageCollectionName = 'profile';

    /**
     * Returns the associated feed messages for the user.
     *
     * @return HasMany
     */
    function feedMessages(): HasMany
    {
        return $this->hasMany(FeedMessage::class);
    }

    /**
     * Returns the user's activity status based on their sessions.
     *
     * @return UserActivityStatus
     */
    public function getActivityStatus(): UserActivityStatus
    {
        /** @var Session $session */
        $session = $this->sessions()
            ->orderBy('last_activity_at', 'desc')
            ->first();

        if ($session === null)
            return UserActivityStatus::Offline();

        // Seen within the last 5 minutes
        if ($session->last_activity_at >= now()->subMinutes(5)) {
            return UserActivityStatus::Online();
        } // Seen within the last 15 minutes
        else if ($session->last_activity_at >= now()->subMinutes(15)) {
            return UserActivityStatus::SeenRecently();
        }

        return UserActivityStatus::Offline();
    }

    /**
     * Returns the associated sessions for the user
     *
     * @return HasMany
     */
    function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->profileImageCollectionName)
            ->singleFile()
            ->useFallbackUrl('https://ui-avatars.com/api/?name=' . $this->username . '&color=000000&background=e0e0e0&length=1&bold=true');

        $this->addMediaCollection($this->bannerImageCollectionName)
            ->singleFile()
            ->withResponsiveImages();
    }

    /**
     * Returns the Anime that the user has added to their favorites.
     *
     * @return BelongsToMany
     */
    function favoriteAnime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, UserFavoriteAnime::class, 'user_id', 'anime_id');
    }

    /**
     * Returns the User-Reminder-Anime relationship for the user.
     *
     * @return HasMany
     */
    function userReminderAnime(): HasMany
    {
        return $this->hasMany(UserReminderAnime::class);
    }

    /**
     * Returns the content of the calendar generated from the user's anime reminders.
     *
     * @return string
     */
    function getCalendar(): string
    {
        /** @var Anime[] $animes */
        $animes = $this->reminderAnime()->get();

        // Find location of cached data
        $cacheKey = self::TABLE_NAME . '-name-reminders-id-' . $this->id . '-reminder_count-' . count($animes);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CALENDAR_SECONDS, function() use ($animes) {
            $appName = Env('APP_NAME');
            $productIdentifier = '-//Kurozora B.V.//' . $appName . '//' . strtoupper(config('app.locale'));

            $calendar = Calendar::create(UserReminderAnime::CAL_NAME);
            $calendar->description(UserReminderAnime::CAL_DESCRIPTION)
                ->productIdentifier($productIdentifier)
                ->refreshInterval(UserReminderAnime::CAL_REFRESH_INTERVAL)
                ->appendProperty(TextProperty::create('CALSCALE', 'GREGORIAN'))
                ->appendProperty(TextProperty::create('X-APPLE-CALENDAR-COLOR', '#FF9300'))
                ->appendProperty(TextProperty::create('COLOR', 'orange'))
                ->appendProperty(TextProperty::create('ORGANIZER', 'kurozoraapp@gmail.app')
                    ->addParameter(Parameter::create('CN', 'Kurozora')));

            $startDate = Carbon::now()->startOfWeek()->subWeeks(1);
            $endDate = Carbon::now()->endOfWeek()->addWeeks(2);
            $whereBetween = [$startDate, $endDate];

            foreach ($animes as $anime) {
                $episodes = $anime->getEpisodes($whereBetween);

                foreach ($episodes as $episode) {
                    $uniqueIdentifier = Uuid::uuid4() . '@kurozora.app';
                    $eventName = $anime->title . ' Episode ' . $episode->number;
                    $startsAt = $episode->first_aired->setTimezone('Asia/Tokyo');
                    $endsAt = $episode->first_aired->addMinutes($anime->runtime)->setTimezone('Asia/Tokyo');

                    // Create event
                    $calendarEvent = Event::create($eventName)
                        ->description($episode->overview)
                        ->organizer('kurozoraapp@gmail.com', 'Kurozora')
                        ->startsAt($startsAt)
                        ->endsAt($endsAt)
                        ->uniqueIdentifier($uniqueIdentifier);

                    // Add custom properties
                    $calendarEvent->appendProperty(TextProperty::create('URL', route('anime.details', $anime)))
                        ->appendProperty(TextProperty::create('X-APPLE-TRAVEL-ADVISORY-BEHAVIOR', 'AUTOMATIC'));

                    // Add alerts
                    $firstReminderMessage = $eventName . ' starts in ' . UserReminderAnime::CAL_FIRST_ALERT_MINUTES . ' minutes.';
                    $secondReminderMessage = $eventName . ' starts in ' . UserReminderAnime::CAL_SECOND_ALERT_MINUTES . ' minutes.';
                    $thirdReminderMessage = $eventName . ' starts in ' . UserReminderAnime::CAL_THIRD_ALERT_DAY . ' day.';

                    $firstAlert = Alert::minutesBeforeStart(UserReminderAnime::CAL_FIRST_ALERT_MINUTES)
                        ->message($firstReminderMessage)
                        ->appendProperty(TextProperty::create('UID', Uuid::uuid4() . '@kurozora.app'));
                    $secondAlert = Alert::minutesBeforeStart(UserReminderAnime::CAL_SECOND_ALERT_MINUTES)
                        ->message($secondReminderMessage)
                        ->appendProperty(TextProperty::create('UID', Uuid::uuid4() . '@kurozora.app'));
                    $thirdAlert = Alert::minutesBeforeStart(UserReminderAnime::CAL_THIRD_ALERT_DAY)
                        ->message($thirdReminderMessage)
                        ->appendProperty(TextProperty::create('UID', Uuid::uuid4() . '@kurozora.app'));

                    $calendarEvent->alert($firstAlert)
                        ->alert($secondAlert)
                        ->alert($thirdAlert);

                    // Add event to calendar
                    $calendar->event($calendarEvent);
                }
            }

            // Export calendar
            return $calendar->get();
        });
    }

    /**
     * Returns the Anime that the user has added to their reminders.
     *
     * @return BelongsToMany
     */
    function reminderAnime(): BelongsToMany
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
    function isTracking(Anime $anime): bool
    {
        return $this->library()->where('anime_id', $anime->id)->exists();
    }

    /**
     * Returns the Anime items in the user's library.
     *
     * @return BelongsToMany
     */
    function library(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, UserLibrary::class, 'user_id', 'anime_id')
            ->withPivot('status');
    }

    /**
     * Returns the Anime that the user is moderating.
     *
     * @return BelongsToMany
     */
    function moderatingAnime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, AnimeModerator::class, 'user_id', 'anime_id')
            ->withPivot('created_at');
    }

    /**
     * Returns a boolean indicating whether the user has watched the given episode.
     *
     * @param AnimeEpisode $episode
     *
     * @return bool
     */
    function hasWatched(AnimeEpisode $episode): bool
    {
        return $this->watchedAnimeEpisodes()->where('episode_id', $episode->id)->exists();
    }

    /**
     * Returns the watched Episode items.
     *
     * @return BelongsToMany
     */
    function watchedAnimeEpisodes(): BelongsToMany
    {
        return $this->belongsToMany(AnimeEpisode::class, UserWatchedEpisode::class, 'user_id', 'episode_id');
    }

    /**
     * Returns the associated badges for the user
     *
     * @return BelongsToMany
     */
    function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, UserBadge::class, 'user_id', 'badge_id');
    }

    /**
     * Creates a new session for the user.
     *
     * Available options:
     * - `ip_address`: the IP address used to create the session, request IP is used by default.
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
    function createSession(array $options = []): Session
    {
        $options = new OptionsBag($options);

        // Determine the IP address to use
        $ipAddress = $options->get('ip_address');

        if ($ipAddress === null) {
            $ipAddress = request()->ip();
        }

        try {
            $sessionID = request()->session()->getId();
        } catch (RuntimeException $e) {
            // Laravel session doesn't exist on API requests, so null is used instead.
            $sessionID = null;
        }

        $session = Session::create([
            'session_id'        => $sessionID,
            'user_id'           => $this->id,
            'secret'            => Str::random(128),
            'expires_at'        => now()->addDays(Session::VALID_FOR_DAYS),
            'last_activity_at'  => now(),
            'ip_address'        => $ipAddress,

            // Platform information
            'platform'          => $options->get('platform'),
            'platform_version'  => $options->get('platform_version'),
            'device_vendor'     => $options->get('device_vendor'),
            'device_model'      => $options->get('device_model'),
        ]);

        // Dispatch job to retrieve location
        if ($options->get('retrieve_location', true)) {
            dispatch(new FetchSessionLocation($session));
        }

        // Send notification
        if ($options->get('notify', true)) {
            $this->notify(new NewSession($session));
        }

        return $session;
    }

    /**
     * Attempts to delete the user's current session.
     * This method works only on routes with Laravel session in the middleware.
     *
     * @return bool|null
     * @throws RuntimeException
     */
    function deleteCurrentSession(): bool|null
    {
        try {
            $sessionID = request()->session()->getId();
        } catch (RuntimeException $exception) {
            throw new RuntimeException($exception->getMessage() . ' Use the delete method on the session model itself if attempting to delete through the API.');
        }

        return $this->sessions()
            ->where('session_id', $sessionID)
            ->delete();
    }

    /**
     * Returns the associated threads for the user
     *
     * @return HasMany
     */
    function threads(): HasMany
    {
        return $this->hasMany(ForumThread::class);
    }

    /**
     * Returns a list of badges that the user has assigned to them
     *
     * @return array
     */
    public function getBadges(): array
    {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_BADGES, $this->id);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_BADGES_SECONDS, function () {
            return Badge::join(UserBadge::TABLE_NAME, function ($join) {
                $join->on(UserBadge::TABLE_NAME . '.badge_id', '=', Badge::TABLE_NAME . '.id');
            })
                ->where([
                    [UserBadge::TABLE_NAME . '.user_id', '=', $this->id]
                ])
                ->get();
        });
    }

    /**
     * Returns the amount of followers the user has
     *
     * @return int
     */
    public function getFollowerCount(): int
    {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWER_COUNT, $this->id);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWER_COUNT_SECONDS, function () {
            return $this->followers()->count();
        });
    }

    /**
     * Get the user's followers
     *
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'following_user_id', 'user_id');
    }

    /**
     * Returns the amount of users the user follows
     *
     * @return int
     */
    public function getFollowingCount(): int
    {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_FOLLOWING_COUNT, $this->id);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_FOLLOWING_COUNT_SECONDS, function () {
            return $this->following()->count();
        });
    }

    /**
     * Get the user's following users.
     *
     * @return BelongsToMany
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'user_id', 'following_user_id');
    }

    /**
     * Returns the total amount of reputation the user has
     *
     * @return int
     */
    public function getReputationCount(): int
    {
        // Find location of cached data
        $cacheKey = sprintf(self::CACHE_KEY_REPUTATION_COUNT, $this->id);

        // Retrieve or save cached result
        $repCount = Cache::remember($cacheKey, self::CACHE_KEY_REPUTATION_COUNT_SECONDS, function () {
            $foundRep = UserReputation::where('given_user_id', $this->id)->sum('amount');

            if ($foundRep === null) return 0;
            return (int)$foundRep;
        });

        return (int)$repCount;
    }

    /**
     * Checks the cooldown whether the user can do a MAL import.
     *
     * @return bool
     */
    function canDoMALImport(): bool
    {
        if (!$this->last_mal_import_at)
            return true;

        if ($this->last_mal_import_at > now()->subDays(config('mal-import.cooldown_in_days')))
            return false;

        return true;
    }

    /**
     * Returns the APN token(s) for the user.
     *
     * @return array
     */
    public function routeNotificationForApn(): array
    {
        return $this->sessions()
            ->whereNotNull('apn_device_token')
            ->pluck('apn_device_token')
            ->toArray();
    }

    /**
     * Returns the store receipt of the user.
     *
     * @return HasOne
     */
    function receipt(): HasOne
    {
        return $this->hasOne(UserReceipt::class);
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}
