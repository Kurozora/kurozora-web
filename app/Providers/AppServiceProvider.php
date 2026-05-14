<?php

namespace App\Providers;

use App\Models\Anime;
use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Observers\AnimeObserver;
use App\Policies\NotificationPolicy;
use App\Providers\SocialiteProviders\AppleProvider;
use App\Services\AppStoreService;
use App\Services\LinkPreviewService;
use App\Services\ReputationService;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use RoachPHP\Roach;
use SocialiteProviders\Manager\SocialiteWasCalled;
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
            DB::whenQueryingForLongerThan(1000, function (Connection $connection, QueryExecuted $query) {
                logger()->warning("Database queries exceeded 1 second ($query->time) on {$connection->getName()}", [
                    'sql' => $query->sql
                ]);
            });
        }

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

        // Register observers
        Anime::observe(AnimeObserver::class);

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
        // Register explore category scope. This makes sure only enabled categories are included.
        $this->app->bind('explore.only_enabled', function () {
            return true;
        });

        // Register App Store service.
        $this->app->bind(AppStoreService::class, function () {
            return new AppStoreService();
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
    }
}
