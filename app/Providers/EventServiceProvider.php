<?php

namespace App\Providers;

use App\Events\AnimeViewed;
use App\Events\Event;
use App\Listeners\EventListener;
use App\Listeners\FetchAnimeActors;
use App\Listeners\FetchAnimeDetails;
use App\Listeners\FetchAnimeImages;
use App\Listeners\FetchBaseAnimeEpisodes;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
//        \Illuminate\Auth\Events\Registered::class => [
//            \Illuminate\Auth\Listeners\SendEmailVerificationNotification::class,
//        ],

        Event::class => [
            EventListener::class,
        ],

        AnimeViewed::class => [
            FetchAnimeDetails::class,
            FetchBaseAnimeEpisodes::class,
            FetchAnimeImages::class,
            FetchAnimeActors::class,
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
