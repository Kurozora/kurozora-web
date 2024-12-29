<?php

namespace App\Models;

use App\Enums\MediaCollection;
use App\Enums\UserActivityStatus;
use App\Enums\UserLibraryStatus;
use App\Helpers\OptionsBag;
use App\Jobs\FetchSessionLocation;
use App\Notifications\NewSession;
use App\Notifications\ResetPassword as ResetPasswordNotification;
use App\Notifications\VerifyEmail as VerifyEmailNotification;
use App\Parsers\MentionParser;
use App\Traits\HeartActionTrait;
use App\Traits\InteractsWithMediaExtension;
use App\Traits\Model\Favoriter;
use App\Traits\Model\Followable;
use App\Traits\Model\Follower;
use App\Traits\Model\HasSlug;
use App\Traits\Model\HasViews;
use App\Traits\Model\Impersonatable;
use App\Traits\Model\Reminder;
use App\Traits\Model\Tracker;
use App\Traits\SearchFilterable;
use App\Traits\Web\Auth\TwoFactorAuthenticatable;
use Carbon\Carbon;
use Cog\Contracts\Love\Reacterable\Models\Reacterable as ReacterableContract;
use Cog\Laravel\Love\Reacterable\Models\Traits\Reacterable;
use DB;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;
use Markdown;
use Ramsey\Uuid\Uuid;
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
use Spatie\Sluggable\SlugOptions;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;
use Throwable;
use URL;
use Xetaio\Mentions\Models\Traits\HasMentionsTrait;

class User extends Authenticatable implements HasMedia, MustVerifyEmail, ReacterableContract, Sitemapable
{
    use Authorizable,
        Favoriter,
        Follower,
        Followable,
        HasApiTokens,
        HasFactory,
        HasJsonRelationships,
        HasMentionsTrait,
        HasPermissions,
        HasRoles,
        HasSlug,
        HasUuids,
        HasViews,
        HeartActionTrait,
        Impersonatable,
        InteractsWithMedia,
        InteractsWithMediaExtension,
        LogsActivity,
        Notifiable,
        MassPrunable,
        Reacterable,
        Reminder,
        Searchable,
        SearchFilterable,
        Tracker,
        TwoFactorAuthenticatable;

    // Cache user's calendar
    const int|float CACHE_KEY_CALENDAR_SECONDS = 60 * 60 * 24;

    // Cache user's reputation count
    const string CACHE_KEY_REPUTATION_COUNT = 'user-reputation-%d';
    const int|float CACHE_KEY_REPUTATION_COUNT_SECONDS = 10 * 60;

    // Length limits
    const int MAX_BIOGRAPHY_LENGTH = 500;
    const int MINIMUM_SLUG_LENGTH = 3;
    const int MAXIMUM_SLUG_LENGTH = 30;
    const int MINIMUM_USERNAME_LENGTH = 1;
    const int MAXIMUM_USERNAME_LENGTH = 30;

    // Table name
    const string TABLE_NAME = 'users';
    protected $table = self::TABLE_NAME;

    // Remove column guards
    protected $guarded = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'anime_imported_at' => 'datetime',
            'manga_imported_at' => 'datetime',
            'subscribed_at' => 'datetime',
            'is_developer' => 'bool',
            'is_early_supporter' => 'bool',
            'is_staff' => 'bool',
            'is_pro' => 'bool',
            'is_subscribed' => 'bool',
            'is_verified' => 'bool',
            'can_change_username' => 'bool',
        ];
    }

    /**
     * Bootstrap the model and its traits.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (User $user) {
            // Strip HTML tags
            $user->biography = strip_tags(trim(Markdown::parse(nl2br($user->biography))));

            // Parse user mentions
            $parser = new MentionParser($user, [
                'notify' => false,
            ]);
            $user->biography_markdown = $parser->parse($user->biography);

            // Parse user mentions
            $user->biography_html = trim(Markdown::parse(nl2br($user->biography_markdown)));
        });
    }

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array
     */
    public function uniqueIds(): array
    {
        return [
            'uuid',
        ];
    }

    /**
     * Registers the media collections for the model.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(MediaCollection::Profile)
            ->singleFile()
            ->useFallbackUrl('https://ui-avatars.com/api/?name=' . $this->username . '&color=FFFFFF&background=AAAAAA&length=1&bold=true&size=256');
        $this->addMediaCollection(MediaCollection::Banner)
            ->singleFile();
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
            ->skipGenerateWhen(function () {
                return !(empty($this->slug) || $this->can_change_username);
            })
            ->usingSeparator('_')
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
     * The orderable properties.
     *
     * @return array[]
     */
    public static function webSearchOrders(): array
    {
        $order = [
            'username' => [
                'title' => __('Name'),
                'options' => [
                    'Default' => null,
                    'A-Z' => 'asc',
                    'Z-A' => 'desc',
                ],
                'selected' => null,
            ],
            'created_at' => [
                'title' => __('Join Date'),
                'options' => [
                    'Default' => null,
                    'Newest' => 'desc',
                    'Oldest' => 'asc',
                ],
                'selected' => null,
            ],
        ];

        return $order;
    }

    /**
     * The filterable properties.
     *
     * @return array[]
     */
    public static function webSearchFilters(): array
    {
        return [];
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
            'letter' => str_index($this->username),
            'username' => $this->username,
            'biography' => $this->biography,
            'is_developer' => $this->is_developer,
            'is_staff' => $this->is_staff,
            'is_early_supporter' => $this->is_early_supporter,
            'is_pro' => $this->is_pro,
            'is_subscribed' => $this->is_subscribed,
            'is_verified' => $this->is_verified,
            'subscribed_at' => $this->subscribed_at?->timestamp,
            'anime_imported_at' => $this->anime_imported_at?->timestamp,
            'manga_imported_at' => $this->manga_imported_at?->timestamp,
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
            ['created_at', '<', now()->subDays(30)],
        ]);
    }

    /**
     * Determine if the user can impersonate another user.
     *
     * @return bool
     */
    public function canImpersonate(): bool
    {
        return $this->hasRole('superAdmin');
    }

    /**
     * Determine if the user can be impersonated.
     *
     * @return bool
     */
    public function canBeImpersonated(): bool
    {
        return !$this->hasRole('superAdmin');
    }

    public function getNameAttribute(): string
    {
        return $this->username;
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
    public function mediaRatings(): HasMany
    {
        return $this->hasMany(MediaRating::class);
    }

    /**
     * Returns the anime ratings the user has.
     *
     * @param null|string $type
     *
     * @return bool
     */
    public function clearRatings(?string $type = null): bool
    {
        return $this->mediaRatings()
            ->when($type != null, function ($query) use ($type) {
                $query->where('model_type', '=', $type);
            })
            ->forceDelete();
    }

    /**
     * Returns the anime ratings the user has.
     *
     * @return HasMany
     */
    public function animeRatings(): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', Anime::class);
    }

    /**
     * Returns the game ratings the user has.
     *
     * @return HasMany
     */
    public function gameRatings(): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', Game::class);
    }

    /**
     * Returns the manga ratings the user has.
     *
     * @return HasMany
     */
    public function mangaRatings(): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', Manga::class);
    }

    /**
     * Returns the episode ratings the user has.
     *
     * @return HasMany
     */
    public function episodeRatings(): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', Episode::class);
    }

    /**
     * Returns the song ratings the user has.
     *
     * @return HasMany
     */
    public function songRatings(): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', Song::class);
    }

    /**
     * Returns the studio ratings the user has.
     *
     * @return HasMany
     */
    public function studioRatings(): HasMany
    {
        return $this->mediaRatings()
            ->where('model_type', '=', Studio::class);
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
     * Toggle the pinned state of a feed message.
     *
     * @param FeedMessage $feedMessage
     *
     * @return bool
     * @throws Throwable
     */
    public function togglePin(FeedMessage $feedMessage): bool
    {
        // Database transaction for atomicity
        return DB::transaction(function () use ($feedMessage) {
            // Unpin if the same message already pinned
            if ($feedMessage->is_pinned) {
                $feedMessage->update([
                    'is_pinned' => false,
                ]);
                return false;
            }

            // Unpin any currently pinned message
            $this->feed_messages()
                ->where('is_pinned', true)
                ->update([
                    'is_pinned' => false,
                ]);

            // Pin the new message
            $feedMessage->update([
                'is_pinned' => true,
            ]);

            return true;
        });
    }

    /**
     * Returns the user's activity status based on their sessions.
     *
     * @return UserActivityStatus
     */
    public function getActivityStatusAttribute(): UserActivityStatus
    {
        // The token relation is eager loaded elsewhere with
        // the following constraints: orderBy('last_used_at', 'desc'),
        // limit(1), and select('last_used_at').
        $personalAccessToken = $this->tokens->first();
        $personalAccessTokenLastUsedAt = $personalAccessToken?->last_used_at;

        // The session relation is eager loaded elsewhere with
        // the following constraints: orderBy('last_activity', 'desc'),
        // limit(1), and select('last_activity').
        $session = $this->sessions->first();
        $sessionLastActivity = Carbon::createFromTimestamp($session?->last_activity ?? now()->timestamp);

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
     * Returns the content of the calendar generated from the user's anime reminders.
     *
     * @return string
     */
    function getCalendar(): string
    {
        $reminders = $this->reminders;

        // Find location of cached data
        $cacheKey = self::TABLE_NAME . '-name-reminders-id-' . $this->id . '-reminder_count-' . $reminders->count();

        // Retrieve or save cached result
        return Cache::remember($cacheKey, self::CACHE_KEY_CALENDAR_SECONDS, function () use ($reminders) {
            $appName = config('app.name');
            $appDomain = config('app.domain');
            $appLocale = config('app.locale');
            $productIdentifier = '-//' . $appName . '//' . $appName . '//' . strtoupper($appLocale);

            $calendar = Calendar::create($appName);
            $calendar->description(__('The Kurozora calendar group contains all reminders you have subscribed to in the Kurozora app.'))
                ->productIdentifier($productIdentifier)
                ->refreshInterval(UserReminder::CAL_REFRESH_INTERVAL)
                ->appendProperty(TextProperty::create('CALSCALE', 'GREGORIAN'))
                ->appendProperty(TextProperty::create('X-APPLE-CALENDAR-COLOR', '#FF9300'))
                ->appendProperty(TextProperty::create('COLOR', 'orange'))
                ->appendProperty(TextProperty::create('ORGANIZER', 'reminder@' . $appDomain)
                    ->addParameter(Parameter::create('CN', $appName)));

            foreach ($reminders as $reminder) {
                /** @var Anime $anime */
                $anime = $reminder->remindable;
                $episodes = $anime->episodes;

                foreach ($episodes as $episode) {
                    $uniqueIdentifier = Uuid::uuid4() . '@' . $appDomain;
                    $eventName = $anime->title . ' Episode ' . $episode->number . ' (' . $episode->number_total . ')';
                    $startsAt = $episode->started_at->setTimezone('Asia/Tokyo');
                    $endsAt = $episode->ended_at->setTimezone('Asia/Tokyo');

                    // Create event
                    $calendarEvent = Event::create($eventName)
                        ->description($episode->synopsis ?? $anime->synopsis ?? '')
                        ->organizer('reminder@' . $appDomain, $appName)
                        ->startsAt($startsAt)
                        ->endsAt($endsAt)
                        ->uniqueIdentifier($uniqueIdentifier);

                    // Add custom properties
                    $calendarEvent->appendProperty(TextProperty::create('URL', route('anime.details', $anime)))
                        ->appendProperty(TextProperty::create('X-APPLE-TRAVEL-ADVISORY-BEHAVIOR', 'AUTOMATIC'));

                    // Add alerts
                    $firstReminderMessage = $eventName . ' starts in ' . UserReminder::CAL_FIRST_ALERT_MINUTES . ' minutes.';
                    $secondReminderMessage = $eventName . ' starts in ' . UserReminder::CAL_SECOND_ALERT_MINUTES . ' minutes.';
                    $thirdReminderMessage = $eventName . ' starts in ' . UserReminder::CAL_THIRD_ALERT_DAY . ' day.';

                    $firstAlert = Alert::minutesBeforeStart(UserReminder::CAL_FIRST_ALERT_MINUTES)
                        ->message($firstReminderMessage)
                        ->appendProperty(TextProperty::create('UID', Uuid::uuid4() . '@' . $appDomain));
                    $secondAlert = Alert::minutesBeforeStart(UserReminder::CAL_SECOND_ALERT_MINUTES)
                        ->message($secondReminderMessage)
                        ->appendProperty(TextProperty::create('UID', Uuid::uuid4() . '@' . $appDomain));
                    $thirdAlert = Alert::minutesBeforeStart(UserReminder::CAL_THIRD_ALERT_DAY)
                        ->message($thirdReminderMessage)
                        ->appendProperty(TextProperty::create('UID', Uuid::uuid4() . '@' . $appDomain));

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
     * Returns a boolean indicating whether the user has watched the given season.
     *
     * @param Season $season The season to be searched for in the user's watched list.
     *
     * @return bool
     */
    function hasWatchedSeason(Season $season): bool
    {
        return $this->episodes()->where('season_id', $season->id)->count() === $season->episodes()->withoutGlobalScopes()->count();
    }

    /**
     * Returns the user's watched Episode models.
     *
     * @return BelongsToMany
     */
    function episodes(): BelongsToMany
    {
        return $this->belongsToMany(Episode::class, UserWatchedEpisode::class, 'user_id', 'episode_id')
            ->withTimestamps();
    }

    /**
     * Returns the user's UserWatchedEpisode models.
     *
     * @return HasMany
     */
    function user_watched_episodes(): HasMany
    {
        return $this->hasMany(UserWatchedEpisode::class);
    }

    /**
     * Get the user's up-next episodes.
     *
     * @param null|Anime $anime
     *
     * @return Builder
     */
    function up_next_episodes(?Anime $anime = null): Builder
    {
        $subquery = Episode::join(Season::TABLE_NAME, Episode::TABLE_NAME . '.season_id', '=', Season::TABLE_NAME . '.id')
            ->join(Anime::TABLE_NAME, Season::TABLE_NAME . '.anime_id', '=', Anime::TABLE_NAME . '.id')
            ->join(UserLibrary::TABLE_NAME, function ($join) use ($anime) {
                $join->on(UserLibrary::TABLE_NAME . '.trackable_id', '=', Anime::TABLE_NAME . '.id')
                    ->where(UserLibrary::TABLE_NAME . '.trackable_type', '=', Anime::class)
                    ->when($anime, function($query) use ($anime) {
                        $query->where(UserLibrary::TABLE_NAME . '.trackable_id', '=', $anime->id);
                    })
                    ->where(UserLibrary::TABLE_NAME . '.user_id', '=', 2)
                    ->where(UserLibrary::TABLE_NAME . '.status', '=', UserLibraryStatus::InProgress);
            })
            ->leftJoin(UserWatchedEpisode::TABLE_NAME, function ($join) {
                $join->on(UserWatchedEpisode::TABLE_NAME . '.episode_id', '=', Episode::TABLE_NAME . '.id')
                    ->where(UserWatchedEpisode::TABLE_NAME . '.user_id', '=', 2);
            })
            ->whereNull(UserWatchedEpisode::TABLE_NAME . '.id') // Episode is not watched
            ->where(Episode::TABLE_NAME . '.started_at', '<=', now()) // Episode has already aired
            ->select([DB::raw('MIN(' . Episode::TABLE_NAME . '.id) as episode_id'), Season::TABLE_NAME . '.anime_id'])
            ->groupBy(Season::TABLE_NAME . '.anime_id');

        return Episode::with([
            'anime' => function ($query) {
                $query->with(['media', 'translation']);
            },
            'media',
            'season' => function ($query) {
                $query->with(['translation']);
            },
            'translation'
        ])
            ->joinSub($subquery, 'subquery', function ($join) {
                $join->on(Episode::TABLE_NAME . '.id', '=', 'subquery.episode_id');
            })
            ->orderBy(Episode::TABLE_NAME . '.started_at');
    }

    /**
     * Returns the associated badges for the user
     *
     * @return BelongsToMany
     */
    function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, UserBadge::class)
            ->withTimestamps();
    }

    /**
     * Relation to UserBadge model directly
     *
     * @return HasMany
     */
    public function user_badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
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
     * @param array                       $options
     * @param bool                        $notify
     *
     * @return SessionAttribute
     */
    function createSessionAttributes(Session|PersonalAccessToken $model, array $options = [], bool $notify = false): SessionAttribute
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
        if ($notify) {
            $this->notify(new NewSession($sessionAttribute));
        }

        return $sessionAttribute;
    }

    /**
     * Get the entity's notifications.
     *
     * @return MorphMany
     */
    public function notifications(): MorphMany
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * Get the user's recaps.
     *
     * @return HasMany
     */
    public function recaps(): HasMany
    {
        return $this->hasMany(Recap::class);
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
            return (int) $foundRep;
        });

        return (int) $repCount;
    }

    /**
     * Checks the cooldown whether the user can do an anime import.
     *
     * @return bool
     */
    function canDoAnimeImport(): bool
    {
        if (!$this->anime_imported_at) {
            return true;
        }

        if ($this->anime_imported_at > now()->subDays(config('import.cooldown_in_days'))) {
            return false;
        }

        return true;
    }

    /**
     * Checks the cooldown whether the user can do a manga import.
     *
     * @return bool
     */
    function canDoMangaImport(): bool
    {
        if (!$this->manga_imported_at) {
            return true;
        }

        if ($this->manga_imported_at > now()->subDays(config('import.cooldown_in_days'))) {
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
     * @param string $token
     *
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
        return \Spatie\Sitemap\Tags\Url::create(route('profile.details', $this))
            ->setChangeFrequency('daily')
            ->setLastModificationDate($this->updated_at);
    }
}
