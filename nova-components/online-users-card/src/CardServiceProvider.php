<?php

namespace Kurozora\OnlineUsersCard;

use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class CardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void
    {
        Nova::serving(function (ServingNova $event) {
            Nova::script('online-users-card', __DIR__ . '/../dist/js/card.js');
        });
    }

    /**
     * Registers any application services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }
}
