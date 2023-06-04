<?php

namespace App\Providers;

use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use RoachPHP\Roach;

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
        /*
         * This snippet logs the amount of executed queries per request ..
         * .. to the config.
         */
        DB::listen(function ($query) {
            $currentConfigValue = Config::get(self::$queryCountConfigKey);

            if ($currentConfigValue == null) {
                Config::set(self::$queryCountConfigKey, 1);
            } else {
                Config::set(self::$queryCountConfigKey, $currentConfigValue + 1);
            }
        });

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
    }
}
