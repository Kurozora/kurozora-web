<?php

namespace App\Providers;

use App\Events\AnimeViewed;
use App\Events\BareBonesAnimeAdded;
use App\Events\BareBonesMangaAdded;
use App\Events\CharacterViewed;
use App\Events\EpisodeViewed;
use App\Events\Event;
use App\Events\GameViewed;
use App\Events\MangaViewed;
use App\Events\PersonViewed;
use App\Events\SeasonViewed;
use App\Events\SongViewed;
use App\Events\StudioViewed;
use App\Events\UserViewed;
use App\Listeners\AnimeViewedListener;
use App\Listeners\BareBonesAnimeAddedListener;
use App\Listeners\BareBonesMangaAddedListener;
use App\Listeners\CharacterViewedListener;
use App\Listeners\EpisodeViewedListener;
use App\Listeners\EventListener;
use App\Listeners\GameViewedListener;
use App\Listeners\MangaViewedListener;
use App\Listeners\MediaHasBeenAddedListener;
use App\Listeners\PersonViewedListener;
use App\Listeners\SeasonViewedListener;
use App\Listeners\SongViewedListener;
use App\Listeners\StudioViewedListener;
use App\Listeners\UserViewedListener;
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
        AnimeViewed::class => [
            AnimeViewedListener::class
        ],
        BareBonesAnimeAdded::class => [
            BareBonesAnimeAddedListener::class
        ],

        // Character events
        CharacterViewed::class => [
            CharacterViewedListener::class
        ],

        // Episode events
        EpisodeViewed::class => [
            EpisodeViewedListener::class
        ],

        // Game events
        GameViewed::class => [
            GameViewedListener::class
        ],

        // Manga events
        MangaViewed::class => [
            MangaViewedListener::class
        ],
        BareBonesMangaAdded::class => [
            BareBonesMangaAddedListener::class
        ],

        // Person events
        PersonViewed::class => [
            PersonViewedListener::class
        ],

        // Season events
        SeasonViewed::class => [
            SeasonViewedListener::class
        ],

        // Song events
        SongViewed::class => [
            SongViewedListener::class
        ],

        // Studio events
        StudioViewed::class => [
            StudioViewedListener::class
        ],

        // User events
        UserViewed::class => [
            UserViewedListener::class
        ],

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
    public function boot(): void
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
