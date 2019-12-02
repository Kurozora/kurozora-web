<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \App\Events\Event::class => [
            \App\Listeners\EventListener::class,
        ],

        \App\Events\NewUserSessionEvent::class => [
            \App\Listeners\SendSessionNotification::class,
        ],

        \App\Events\MALImportFinished::class => [
            \App\Listeners\SendMALImportNotification::class,
        ],

        \App\Events\AnimeViewed::class => [
            \App\Listeners\FetchAnimeDetails::class,
            \App\Listeners\FetchBaseAnimeEpisodes::class,
            \App\Listeners\FetchAnimeImages::class,
            \App\Listeners\FetchAnimeActors::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
