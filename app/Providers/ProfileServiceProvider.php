<?php

namespace App\Providers;

use App\Actions\Web\DeleteUser;
use App\Actions\Web\Profile\ImportUserAnimeLibrary;
use App\Actions\Web\Profile\UpdateUserPreferredTvRating;
use App\Actions\Web\UpdateUserPassword;
use App\Actions\Web\UpdateUserProfileInformation;
use App\Contracts\DeletesUsers;
use App\Contracts\UpdatesUserPasswords;
use App\Contracts\UpdatesUserProfileInformation;
use App\Contracts\Web\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use App\Contracts\Web\Profile\ImportsUserAnimeLibrary;
use App\Contracts\Web\Profile\UpdatesUserPreferredTvRating;
use Illuminate\Support\ServiceProvider;

class ProfileServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, TwoFactorAuthenticationProvider::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->app->singleton(ImportsUserAnimeLibrary::class, ImportUserAnimeLibrary::class);
        $this->app->singleton(UpdatesUserPreferredTvRating::class, UpdateUserPreferredTvRating::class);
        $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        $this->app->singleton(DeletesUsers::class, DeleteUser::class);
    }
}
