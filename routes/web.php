<?php

use App\Http\Controllers\API\v1\MiscController;
use App\Http\Controllers\Web\Auth\ImpersonateController;
use App\Http\Controllers\Web\Misc\HealthCheckController;
use App\Http\Livewire\Home;
use App\Http\Livewire\Schedule\Index as ScheduleIndex;
use App\Http\Livewire\Welcome;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get('chat', function () {
    return view('chat');
});

Route::get(RouteServiceProvider::HOME, Home::class)
    ->name('home');

Route::get('welcome', Welcome::class)
    ->name('welcome');

Route::get('health-check', [HealthCheckController::class, 'index'])
    ->name('misc.health-check');

Route::delete('impersonation', [ImpersonateController::class, 'stopImpersonating'])
    ->name('impersonation.stop');

Route::prefix('.well-known')
    ->name('.well-known')
    ->group(function () {
        Route::get('apple-app-site-association', [MiscController::class, 'appleAppSiteAssociation'])
            ->name('.apple-app-site-association');
    });

Route::get('schedule', ScheduleIndex::class)
    ->name('schedule');

// Authentication routes
require 'Web/Authentication.php';

// Landing pages
require 'Web/Anime.php';
require 'Web/AniDB.php';
require 'Web/AniList.php';
require 'Web/AnimePlanet.php';
require 'Web/AniSearch.php';
require 'Web/Charts.php';
require 'Web/Characters.php';
require 'Web/Embed.php';
require 'Web/Episodes.php';
require 'Web/Explore.php';
require 'Web/Games.php';
require 'Web/Genres.php';
require 'Web/IMDB.php';
require 'Web/Kitsu.php';
require 'Web/Library.php';
require 'Web/LiveChart.php';
require 'Web/Manga.php';
require 'Web/Me.php';
require 'Web/MyAnimeList.php';
require 'Web/Notify.php';
require 'Web/People.php';
require 'Web/Platforms.php';
require 'Web/Profile.php';
require 'Web/Recap.php';
require 'Web/Syoboi.php';
require 'Web/Search.php';
require 'Web/Seasons.php';
require 'Web/Songs.php';
require 'Web/Studios.php';
require 'Web/Theme.php';
require 'Web/Theme Store.php';
require 'Web/Trakt.php';
require 'Web/TVDB.php';

// Knowledge Base
require 'Web/Knowledge Base.php';

// Misc pages
require 'Web/Misc.php';

// Legal pages
require 'Web/Legal.php';
