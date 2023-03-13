<?php

namespace App\Providers;

use App\Extensions\KSessionManager;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('session', function ($app) {
            return new KSessionManager($app);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(ConnectionInterface $connection)
    {
        // ...
    }
}
