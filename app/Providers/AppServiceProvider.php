<?php

namespace App\Providers;

use Amirami\Localizator\Services\Parser;
use App\Extensions\KLocalizatorParser;
use App\Models\Anime;
use App\Models\FeedMessage;
use App\Models\MediaRating;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Models\UserBlock;
use App\Models\UserFavorite;
use App\Models\UserFollow;
use App\Models\UserLibrary;
use App\Models\UserNote;
use App\Models\UserReminder;
use App\Models\UserWatchedEpisode;
use App\Observers\AnimeObserver;
use App\Observers\FeedMessageObserver;
use App\Observers\MediaRatingObserver;
use App\Observers\UserStateObserver;
use App\Policies\NotificationPolicy;
use App\Providers\SocialiteProviders\AppleProvider;
use App\Services\AppleMusicService;
use App\Services\AppStoreService;
use App\Services\LinkPreviewService;
use App\Services\ReputationService;
use App\Support\Media\ImageTransformingFileAdder;
use Barryvdh\Debugbar\LaravelDebugbar;
use BeyondCode\QueryDetector\QueryDetector;
use Carbon\Carbon;
use Cog\Laravel\Love\Reaction\Models\Reaction;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Sanctum\Sanctum;
use RoachPHP\Roach;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The running total of database queries executed during the current request.
     *
     * @var int
     */
    public static int $queryCount = 0;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Returns the receiver shifted into the requesting user's preferred timezone.
        Carbon::macro('inUserTimezone', function () {
            /** @var Carbon $this */
            return $this->setTimezone(request()?->attributes->get('formatTimezone', 'UTC'));
        });

        // Returns the requesting user's preferred TV rating, or 4 by default.
        Request::macro('tvRating', function (): int {
            /** @var Request $this */
            return (int) $this->attributes->get('tvRating', 4);
        });

        // Prevent dangerous actions
        DB::prohibitDestructiveCommands(app()->isProduction());
        SeedCommand::prohibit(app()->isProduction());

        // Prevent accessing missing attributes on models
//        Model::preventAccessingMissingAttributes();

        // Log a warning if we spend more than a total of 1000 ms querying.
        if (app()->isLocal()) {
            // Debugbar and QueryDetector register global listeners that capture their
            // instance, so Octane's flush list cannot reset them. The listener would keep
            // feeding a detached copy that grows until the memory limit kills the worker.
            // Keep one instance of each and empty its state before every request instead.
            Event::listen(RequestReceived::class, function (): void {
                if (class_exists(QueryDetector::class)) {
                    app(QueryDetector::class)->emptyQueries();
                }

                if (class_exists(LaravelDebugbar::class)) {
                    foreach (app(LaravelDebugbar::class)->getCollectors() as $collector) {
                        if (method_exists($collector, 'reset')) {
                            $collector->reset();
                        }

                        if (method_exists($collector, 'clear')) {
                            $collector->clear();
                        }
                    }
                }
            });

            DB::whenQueryingForLongerThan(1000, function (Connection $connection, QueryExecuted $query) {
                logger()->warning("Database queries exceeded 1 second ($query->time) on {$connection->getName()}", [
                    'sql' => $query->sql
                ]);
            });

            DB::listen(function (QueryExecuted $query) {
                // Ignore very fast queries
                if ($query->time < 50) {
                    return;
                }

                // Ignore internal/system tables
                if (str($query->sql)->contains([
                    'information_schema',
                    'migrations',
                    'telescope',
                ])) {
                    return;
                }

                // Only analyze SELECTs
                if (! str(strtolower(trim($query->sql)))->startsWith('select')) {
                    return;
                }

                try {
                    $plan = DB::select(
                        'EXPLAIN ANALYZE '.$query->sql,
                        $query->bindings
                    );
                } catch (Throwable $e) {
                    return; // some queries cannot be analyzed
                }

                $planText = json_encode($plan);

                $problems = [];

                // Full table scan detection
                if (str($planText)->contains('"table_scan": true')) {
                    $problems[] = 'Full table scan detected';
                }

                // Filesort
                if (str($planText)->contains('filesort')) {
                    $problems[] = 'Filesort detected (ORDER BY not indexed)';
                }

                // Temporary table
                if (str($planText)->contains('temporary')) {
                    $problems[] = 'Temporary table usage detected';
                }

                // Large row scan heuristic
                if (preg_match('/rows=(\d+)/', $planText, $matches)) {
                    $rows = (int) $matches[1];

                    if ($rows > 10000) {
                        $problems[] = "Large row scan ({$rows} rows)";
                    }
                }

                if (! empty($problems)) {
                    logger()->warning('Potential missing index / poor plan detected', [
                        'time_ms' => $query->time,
                        'issues' => $problems,
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                    ]);
                }
            });
        }

        // Icon rendering
        Blade::directive('svg', function (string $expression): string {
            return "<?php echo icon_sprite($expression); ?>";
        });

        // CSRF verification exceptions
        VerifyCsrfToken::except([
            '/siwa/callback'
        ]);

        // Rate limits
        RateLimiter::for('api', function (Request $request) {
            $method = $request->method();

            return match ($method) {
                'GET' => Limit::perMinutes(1, 3600)->by($method . ':' . $request->user()?->id ?: $request->ip()),
                default => Limit::perMinute(60)->by($method . ':' . ($request->user()?->id ?: $request->ip())),
            };
        });

        RateLimiter::for('api.feed', function (Request $request) {
            return Limit::perMinute(120)->by('feed:' . ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('api.search', function (Request $request) {
            return Limit::perMinute(60)->by('search:' . ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('api.library', function (Request $request) {
            return Limit::perMinute(120)->by('library:' . ($request->user()?->id ?: $request->ip()));
        });

        RateLimiter::for('api.scrobble', function (Request $request) {
            // Trakt parity: one call per second sustained, with a small burst.
            return [
                Limit::perSecond(3)->by('scrobble:burst:' . ($request->user()?->id ?: $request->ip())),
                Limit::perMinute(60)->by('scrobble:' . ($request->user()?->id ?: $request->ip())),
            ];
        });

        // Register observers
        Anime::observe(AnimeObserver::class);
        FeedMessage::observe(FeedMessageObserver::class);
        UserLibrary::observe(UserStateObserver::class);
        MediaRating::observe(MediaRatingObserver::class);
        MediaRating::observe(UserStateObserver::class);
        UserFavorite::observe(UserStateObserver::class);
        UserNote::observe(UserStateObserver::class);
        UserReminder::observe(UserStateObserver::class);
        UserFollow::observe(UserStateObserver::class);
        UserBlock::observe(UserStateObserver::class);
        UserWatchedEpisode::observe(UserStateObserver::class);
        FeedMessage::observe(UserStateObserver::class);
        Reaction::observe(UserStateObserver::class);

        // Register events
        Event::listen(SocialiteWasCalled::class, function (SocialiteWasCalled $event): void {
            $event->extendSocialite('apple', AppleProvider::class);
        });

        /// Register gates
        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole('superAdmin');
        });

        Gate::before(function (User $user, $ability) {
            return $user->hasRole('superAdmin') ? true : null;
        });

        /// Register policy mapping
        Gate::policy(DatabaseNotification::class, NotificationPolicy::class);

        /// Prevent model relationships from lazy loading...
        Model::preventLazyLoading();

        // ...but in production, log the violation instead of throwing an exception...
        if (app()->isProduction()) {
            Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
                // ...as long debug is enabled.
                if (app()->hasDebugModeEnabled()) {
                    $class = get_class($model);

                    info("Attempted to lazy load [$relation] on model [$class].");
                }
            });
        }

        if ($this->app->hasDebugModeEnabled()) {
            /// This snippet logs the number of executed queries per request.
            DB::listen(function (QueryExecuted $query) {
                // - NOTE: For local debug purposes
//                logger()->warning('==== Start ====');
//                logger()->info($query->sql);
//                logger()->warning('==== End ====');
                self::$queryCount++;
            });
        }

        /*
         * Set the default Sanctum classes.
         */
        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register Apple Music service.
        $this->app->singleton(AppleMusicService::class, function () {
            return new AppleMusicService;
        });

        // Register App Store service.
        $this->app->bind(AppStoreService::class, function () {
            return new AppStoreService;
        });

        // Register roach with the app container.
        Roach::useContainer($this->app);

        // Register link preview service.
        $this->app->singleton(LinkPreviewService::class, function () {
            return new LinkPreviewService;
        });

        // Register reputation service.
        $this->app->singleton(ReputationService::class, function () {
            return new ReputationService;
        });

        // Register image transformer.
        $this->app->bind(FileAdder::class, ImageTransformingFileAdder::class);

        // Localizator is a dev dependency.
        if (class_exists(Parser::class)) {
            $this->app->bind(Parser::class, KLocalizatorParser::class);
        }
    }
}
