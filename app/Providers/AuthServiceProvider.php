<?php

namespace App\Providers;

use App\Models\Episode;
use App\Models\PersonalAccessToken;
use App\Models\Session;
use App\Models\User;
use App\Policies\DatabaseNotificationPolicy;
use App\Policies\EpisodePolicy;
use App\Policies\PersonalAccessTokenPolicy;
use App\Policies\SessionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

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
        PersonalAccessToken::class  => PersonalAccessTokenPolicy::class,
        Episode::class              => EpisodePolicy::class
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
            try {
                return $user->hasPermissionTo('*') ? true : null;
            }
            catch(PermissionDoesNotExist $exception) {
                return null;
            }
        });
    }
}
