<?php

namespace App\Providers;

use App\Policies\SessionPolicy;
use App\Policies\UserNotificationPolicy;
use App\Policies\UserPolicy;
use App\Session;
use App\User;
use App\UserNotification;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        User::class             => UserPolicy::class,
        UserNotification::class => UserNotificationPolicy::class,
        Session::class          => SessionPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
