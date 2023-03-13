<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\DatabaseNotificationPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        DatabaseNotification::class => DatabaseNotificationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register the super-admin permission
        Gate::before(function (User $user, $ability) {
            return $user->hasRole('superAdmin') ? true : null;
        });
    }
}
