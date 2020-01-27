<?php

namespace App\Providers;

use App\ForumThread;
use App\Policies\DatabaseNotificationPolicy;
use App\Policies\ForumThreadPolicy;
use App\Policies\SessionPolicy;
use App\Policies\UserPolicy;
use App\Session;
use App\User;
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
        User::class                 => UserPolicy::class,
        Session::class              => SessionPolicy::class,
        ForumThread::class          => ForumThreadPolicy::class
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot() {
        $this->registerPolicies();

        // Register the super-admin permission
        Gate::before(function ($user, $ability) {
            return $user->hasPermissionTo('*') ? true : null;
        });
    }
}
