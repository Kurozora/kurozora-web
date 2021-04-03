<?php

namespace App\Providers;

use App\Models\AnimeEpisode;
use App\Models\ForumThread;
use App\Policies\AnimeEpisodePolicy;
use App\Policies\DatabaseNotificationPolicy;
use App\Policies\ForumThreadPolicy;
use App\Policies\SessionPolicy;
use App\Policies\UserPolicy;
use App\Models\Session;
use App\Models\User;
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
        ForumThread::class          => ForumThreadPolicy::class,
        AnimeEpisode::class         => AnimeEpisodePolicy::class
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
