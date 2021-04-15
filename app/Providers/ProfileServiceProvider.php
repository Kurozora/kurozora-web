<?php

namespace App\Providers;

use App\Actions\Web\Auth\UpdateUserPassword;
use App\Actions\Web\Auth\UpdateUserProfileInformation;
use App\Actions\Web\Auth\DeleteUser;
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
        app()->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        app()->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        app()->singleton(DeletesUsers::class, DeleteUser::class);
    }
}
