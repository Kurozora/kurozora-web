<?php

namespace App\Providers;

use App\Actions\Web\UpdateUserPassword;
use App\Actions\Web\UpdateUserProfileInformation;
use App\Actions\Web\DeleteUser;
use App\Contracts\DeletesUsers;
use App\Contracts\UpdatesUserPasswords;
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
        $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        $this->app->singleton(DeletesUsers::class, DeleteUser::class);
    }
}
