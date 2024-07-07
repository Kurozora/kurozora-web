<?php

namespace App\Providers;

use App\Actions\Web\DeleteLibrary;
use App\Actions\Web\DeleteUser;
use App\Actions\Web\Profile\ImportUserLibrary;
use App\Actions\Web\Profile\UpdateUserPreferredLanguage;
use App\Actions\Web\Profile\UpdateUserPreferredTimezone;
use App\Actions\Web\Profile\UpdateUserPreferredTvRating;
use App\Actions\Web\UpdateUserAccountInformation;
use App\Actions\Web\UpdateUserPassword;
use App\Actions\Web\UpdateUserProfileInformation;
use App\Contracts\DeletesLibraries;
use App\Contracts\DeletesUsers;
use App\Contracts\UpdatesUserAccountInformation;
use App\Contracts\UpdatesUserPasswords;
use App\Contracts\UpdatesUserProfileInformation;
use App\Contracts\Web\Auth\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use App\Contracts\Web\Profile\ImportsUserLibrary;
use App\Contracts\Web\Profile\UpdatesUserPreferredLanguage;
use App\Contracts\Web\Profile\UpdatesUserPreferredTimezone;
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
        $this->app->singleton(ImportsUserLibrary::class, ImportUserLibrary::class);
        $this->app->singleton(UpdatesUserPreferredLanguage::class, UpdateUserPreferredLanguage::class);
        $this->app->singleton(UpdatesUserPreferredTimezone::class, UpdateUserPreferredTimezone::class);
        $this->app->singleton(UpdatesUserPreferredTvRating::class, UpdateUserPreferredTvRating::class);
        $this->app->singleton(UpdatesUserAccountInformation::class, UpdateUserAccountInformation::class);
        $this->app->singleton(UpdatesUserProfileInformation::class, UpdateUserProfileInformation::class);
        $this->app->singleton(UpdatesUserPasswords::class, UpdateUserPassword::class);
        $this->app->singleton(DeletesLibraries::class, DeleteLibrary::class);
        $this->app->singleton(DeletesUsers::class, DeleteUser::class);
    }
}
