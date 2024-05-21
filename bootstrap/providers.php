<?php

return [
    /*
     * Package Service Providers...
     */
    SocialiteProviders\Manager\ServiceProvider::class,

    /*
     * Application Service Providers...
     */
    App\Providers\AppServiceProvider::class,
    Illuminate\Foundation\Support\Providers\EventServiceProvider::class,
    App\Providers\NovaServiceProvider::class,
    App\Providers\ProfileServiceProvider::class,
    App\Providers\TelescopeServiceProvider::class,
    App\Providers\ScoutServiceProvider::class,
    App\Providers\SessionServiceProvider::class,
    App\Providers\UserModelServiceProvider::class,
];
