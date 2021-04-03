<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // The global query count is logged to this config key
    public static $queryCountConfigKey = 'kurozora.query_count';

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        /*
         * This snippet logs the amount of executed queries per request ..
         * .. to the config.
         */
        DB::listen(function ($query) {
            $currentConfigValue = Config::get(self::$queryCountConfigKey);

            if($currentConfigValue == null) {
                Config::set(self::$queryCountConfigKey, 1);
            }
            else Config::set(self::$queryCountConfigKey, $currentConfigValue + 1);
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
