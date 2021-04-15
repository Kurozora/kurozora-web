<?php

namespace App\Providers;

use App\Actions\Web\Auth\UpdateUserProfileInformation;
use App\Contracts\UpdatesUserProfileInformation;
use Illuminate\Support\ServiceProvider;

class ProfileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        app()->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
    }
}
