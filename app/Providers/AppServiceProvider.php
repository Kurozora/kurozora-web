<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use App\Policies\NotificationPolicy;
use App\Services\LinkPreviewService;
use App\Services\ReputationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Connection;
use Illuminate\Database\Console\Seeds\SeedCommand;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use RoachPHP\Roach;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    // The global query count is logged to this config key
    public static string $queryCountConfigKey = 'kurozora.query_count';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
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

        // Register events
        Event::listen(SocialiteWasCalled::class, AppleExtendSocialite::class.'@handle');

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
            /// This snippet logs the number of executed queries per request
            /// to the config.
            DB::listen(function (QueryExecuted $query) {
                $currentConfigValue = Config::get(self::$queryCountConfigKey);

                if ($currentConfigValue == null) {
                    Config::set(self::$queryCountConfigKey, 1);
                } else {
                    // - NOTE: For local debug purposes
//                    logger()->warning('==== Start ====');
//                    logger()->info($query->sql);
//                    logger()->warning('==== End ====');
                    Config::set(self::$queryCountConfigKey, $currentConfigValue + 1);
                }
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
