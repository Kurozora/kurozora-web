<?php

namespace App\Providers;

use App\Events\AnimeViewed;
use App\Events\Event;
use App\Listeners\EventListener;
use App\Listeners\MediaHasBeenAddedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAdded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        Event::class => [
            EventListener::class,
        ],

        AnimeViewed::class => [
        ],

        MediaHasBeenAdded::class => [
            MediaHasBeenAddedListener::class,
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
}
