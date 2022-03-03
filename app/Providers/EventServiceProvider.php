<?php

namespace App\Providers;

use App\Events\AnimeViewed;
use App\Events\Event;
use App\Listeners\EventListener;
use App\Listeners\MediaHasBeenAddedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use SocialiteProviders\Apple\AppleExtendSocialite;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Event::class => [
            EventListener::class,
        ],

        // User events
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Anime events
        AnimeViewed::class => [],

        // Media events
        MediaHasBeenAdded::class => [
            MediaHasBeenAddedListener::class,
        ],

        // Sign in with Apple
        SocialiteWasCalled::class => [
            AppleExtendSocialite::class.'@handle',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
