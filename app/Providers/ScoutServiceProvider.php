<?php

namespace App\Providers;

use App\Extensions\KEngineManager;
use Laravel\Scout\EngineManager;
use Laravel\Scout\ScoutServiceProvider as BaseScoutServiceProvider;

class ScoutServiceProvider extends BaseScoutServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        parent::register();

        $this->app->singleton(EngineManager::class, function ($app) {
            return new KEngineManager($app);
        });
    }
}
