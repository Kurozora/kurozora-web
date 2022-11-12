<?php

namespace App\Models;

use App\Enums\UserActivityStatus;
use App\Helpers\OptionsBag;
use App\Jobs\FetchSessionLocation;
use App\Notifications\NewSession;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use App\Traits\HeartActionTrait;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\HasBannerImage;
use App\Traits\Model\HasProfileImage;
use App\Traits\Web\Auth\TwoFactorAuthenticatable;
use Carbon\Carbon;
use Cog\Contracts\Love\Reacterable\Models\Reacterable as ReacterableContract;
use Cog\Laravel\Love\Reacterable\Models\Traits\Reacterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Ramsey\Uuid\Uuid;
use Request;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\IcalendarGenerator\Components\Alert;
use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Properties\Parameter;
use Spatie\IcalendarGenerator\Properties\TextProperty;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use URL;

class User extends Authenticatable implements HasMedia, MustVerifyEmail, ReacterableContract, Sitemapable
{
    use Authorizable,
        HasApiTokens,
        HasBannerImage,
        HasFactory,
        HasProfileImage,
        HasRoles,
        HasPermissions,
        HasSlug,
        HasUuids,
        HeartActionTrait,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Notifiable,
        MassPrunable,
        Reacterable,
        Searchable,
        TwoFactorAuthenticatable;

    // Cache user's badges
    const CACHE_KEY_BADGES = 'user-badges-%d';
    const CACHE_KEY_BADGES_SECONDS = 120 * 60;

    // Cache user's calendar
    const CACHE_KEY_CALENDAR_SECONDS = 60 * 60 * 24;

    // Cache user's reputation count
    const CACHE_KEY_REPUTATION_COUNT = 'user-reputation-%d';
    const CACHE_KEY_REPUTATION_COUNT_SECONDS = 10 * 60;

    // User biography character limited
    const BIOGRAPHY_LIMIT = 500;

    // Table name
    const TABLE_NAME = 'users';
    protected $table = self::TABLE_NAME;

    // Remove column guards
    protected $guarded = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'last_anime_import_at' => 'datetime',
        'last_manga_import_at' => 'datetime',
        'settings' => 'json',
        'is_pro' => 'bool',
        'is_subscribed' => 'bool',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'banner_image',
        'banner_image_url',
        'profile_image',
        'profile_image_url',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds(): array
    {
        return [
            'uuid'
        ];
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection($this->profileImageCollectionName)
            ->singleFile()
            ->useFallbackUrl('https://ui-avatars.com/api/?name=' . $this->username . '&color=FFFFFF&background=AAAAAA&length=1&bold=true&size=256');

        $this->addMediaCollection($this->bannerImageCollectionName)
            ->singleFile();
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName(): string
    {
        if (Request::wantsJson()) {
            return parent::getRouteKeyName();
        }
        return 'slug';
    }

    /**
     * Get the options for generating the slug.
     *
     * @return SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('username')
            ->saveSlugsTo('slug');
    }

    /**
     * Get the activity options for activity log.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'username' => $this->username,
            'biography' => $this->biography,
            'created_at' => $this->created_at?->timestamp,
            'updated_at' => $this->updated_at?->timestamp,
        ];
    }

    /**
     * Get the prunable model query.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::where([
            ['siwa_id', '=', null],
            ['email_verified_at', '=', null],
            ['is_pro', '=', false],
            ['is_subscribed', '=', false],
            ['email_verified_at', '=', null],
            ['created_at', '<', Carbon::now()->subDays(30)]
        ]);
    }

    /**
     * Returns the language the user has.
     *
     * @return BelongsTo
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id', 'code');
    }

    /**
     * Returns the anime ratings the user has.
     *
     * @return HasMany
     */
    public function media_ratings(): HasMany
    {
        return $this->hasMany(MediaRating::class);
    }

    /**
     * Returns the anime ratings the user has.
     *
     * @return HasMany
     */
    public function anime_ratings(): HasMany
    {
        return $this->hasMany(MediaRating::class)
            ->where('model_type', Anime::class);
    }

    /**
     * Returns the anime ratings the user has.
     *
     * @return HasMany
     */
    public function episode_ratings(): HasMany
    {
        return $this->hasMany(MediaRating::class)
            ->where('model_type', Episode::class);
    }

    /**
     * Returns the associated feed messages for the user.
     *
     * @return HasMany
     */
    function feed_messages(): HasMany
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
        /** @var PersonalAccessToken $personalAccessToken */
        $personalAccessToken = $this->tokens()
            ->orderBy('last_used_at', 'desc')
            ->first('last_used_at');
        $personalAccessTokenLastUsedAt = $personalAccessToken?->last_used_at;

        /** @var Session $session */
        $session = $this->sessions()
            ->orderBy('last_activity', 'desc')
            ->first('last_activity');
        $sessionLastActivity = Carbon::createFromTimestamp($session?->last_activity);

        $activity = max($sessionLastActivity, $personalAccessTokenLastUsedAt);

        if ($activity >= now()->subMinutes(5)) {
            // Seen within the last 5 minutes
            return UserActivityStatus::Online();
        } else if ($activity >= now()->subMinutes(15)) {
            // Seen within the last 15 minutes
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
     * Returns the Anime that the user has added to their favorites.
     *
     * @return BelongsToMany
     */
    function favorite_anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, UserFavoriteAnime::class, 'user_id', 'anime_id')
            ->withTimestamps();
    }

    /**
     * Returns the User-Reminder-Anime relationship for the user.
     *
     * @return HasMany
     */
    function user_reminder_anime(): HasMany
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
        $animes = $this->reminder_anime()->get();

        // Find location of cached data
        $cacheKey = self::TABLE_NAME . '-name-reminders-id-' . $this->id . '-reminder_count-' . count($animes);

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CALENDAR_SECONDS, function() use ($animes) {
            $appName = config('app.name');
            $productIdentifier = '-//Redark//' . $appName . '//' . strtoupper(config('app.locale'));

            $calendar = Calendar::create(UserReminderAnime::CAL_NAME);
            $calendar->description(UserReminderAnime::CAL_DESCRIPTION)
                ->productIdentifier($productIdentifier)
                ->refreshInterval(UserReminderAnime::CAL_REFRESH_INTERVAL)
                ->appendProperty(TextProperty::create('CALSCALE', 'GREGORIAN'))
                ->appendProperty(TextProperty::create('X-APPLE-CALENDAR-COLOR', '#FF9300'))
                ->appendProperty(TextProperty::create('COLOR', 'orange'))
                ->appendProperty(TextProperty::create('ORGANIZER', 'kurozoraapp@gmail.app')
                    ->addParameter(Parameter::create('CN', 'Kurozora')));

            $startDate = now()->startOfWeek()->subWeeks(1);
            $endDate = now()->endOfWeek()->addWeeks(2);
            $whereBetween = [$startDate, $endDate];

            foreach ($animes as $anime) {
                $episodes = $anime->getEpisodes($whereBetween);

                foreach ($episodes as $episode) {
                    $uniqueIdentifier = Uuid::uuid4() . '@kurozora.app';
                    $eventName = $anime->title . ' Episode ' . $episode->number_total;
                    $startsAt = $episode->first_aired->setTimezone('Asia/Tokyo');
                    $endsAt = $episode->first_aired->addSeconds($anime->duration)->setTimezone('Asia/Tokyo');

                    // Create event
                    $calendarEvent = Event::create($eventName)
                        ->description($episode->synopsis)
                        ->organizer('reminder@kurozora.app', 'Kurozora')
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
    function reminder_anime(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class, UserReminderAnime::class, 'user_id', 'anime_id')
            ->withTimestamps();
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
            ->using(UserLibrary::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Returns a boolean indicating whether the user has watched the given episode.
     *
     * @param Episode $episode The episode to be searched for in the user's watched list.
     *
     * @return bool
     */
    function hasWatched(Episode $episode): bool
    {
        return $this->episodes()->where('episode_id', $episode->id)->exists();
    }

    /**
     * Returns the watched Episode items.
     *
     * @return BelongsToMany
     */
    function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class, UserWatchedEpisode::class, 'user_id', 'episode_id')
            ->withTimestamps();
    }

    function user_watched_episode(): HasMany
    {
        return $this->hasMany(UserWatchedEpisode::class);
    }

    /**
     * Returns the associated badges for the user
     *
     * @return BelongsToMany
     */
    function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, UserBadge::class, 'user_id', 'badge_id')
            ->withTimestamps();
    }

    /**
     * Creates a new session attribute for the user.
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
     * @param Session|PersonalAccessToken $model
     * @param array $options
     * @return SessionAttribute
     */
    function createSessionAttributes(Session|PersonalAccessToken $model, array $options = []): SessionAttribute
    {
        $options = new OptionsBag($options);

        // Determine the IP address to use
        $ipAddress = $options->get('ip_address');

        if ($ipAddress === null) {
            $ipAddress = request()->ip();
        }

        $sessionAttribute = $model->session_attribute()->create([
            'ip_address' => $ipAddress,
            'platform' => $options->get('platform'),
            'platform_version' => $options->get('platform_version'),
            'device_vendor' => $options->get('device_vendor'),
            'device_model' => $options->get('device_model'),
        ]);

        // Dispatch job to retrieve location
        if ($options->get('retrieve_location', true)) {
            dispatch(new FetchSessionLocation($sessionAttribute));
        }

        // Send notification
        if ($options->get('notify', true)) {
            $this->notify(new NewSession($sessionAttribute));
        }

        return $sessionAttribute;
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
     * Get the user's followers
     *
     * @return BelongsToMany
     */
    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'following_user_id', 'user_id')
            ->withTimestamps();
    }

    /**
     * Get the user's following users.
     *
     * @return BelongsToMany
     */
    public function following(): BelongsToMany
    {
        return $this->belongsToMany(User::class, UserFollow::class, 'user_id', 'following_user_id')
            ->withTimestamps();
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
     * Checks the cooldown whether the user can do an anime import.
     *
     * @return bool
     */
    function canDoAnimeImport(): bool
    {
        if (!$this->last_anime_import_at) {
            return true;
        }

        if ($this->last_anime_import_at > now()->subDays(config('import.cooldown_in_days'))) {
            return false;
        }

        return true;
    }

    /**
     * Returns the APN token(s) for the user.
     *
     * @return array
     */
    public function routeNotificationForApn(): array
    {
        $tokenIDs = $this->tokens()
            ->pluck('token');

        $apnDeviceToken = SessionAttribute::whereIn('model_id', $tokenIDs)
            ->get()
            ->filter(function (SessionAttribute $sessionAttribute) {
                return $sessionAttribute->apn_device_token != null;
            })
            ->unique('apn_device_token')
            ->pluck('apn_device_token');

        return $apnDeviceToken->toArray();
    }

    /**
     * Returns the store receipts of the user.
     *
     * @return HasMany
     */
    function receipts(): HasMany
    {
        return $this->hasMany(UserReceipt::class, 'user_id', 'uuid');
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification(): void
    {
        // Force root url, because the API will send the request from the API subdomain.
        URL::forceRootUrl(config('app.url'));

        // Notify user
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token): void
    {
        // Force root url, because the API will send the request from the API subdomain.
        URL::forceRootUrl(config('app.url'));

        // Notify user
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Convert the model to its sitemap representation.
     *
     * @return \Spatie\Sitemap\Tags\Url|string|array
     */
    public function toSitemapTag(): \Spatie\Sitemap\Tags\Url|string|array
    {
        return route('profile.details', $this);
    }
}
