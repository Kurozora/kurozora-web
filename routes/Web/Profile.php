<?php

use App\Http\Controllers\Web\MeController;
use App\Http\Controllers\Web\Profile\AnimeLibraryController;
use App\Http\Controllers\Web\Profile\GameLibraryController;
use App\Http\Controllers\Web\Profile\MangaLibraryController;
use App\Http\Controllers\Web\UserProfileController;
use App\Livewire\Profile\Details;
use App\Livewire\Profile\Library\Anime\Favorites as FavoriteAnime;
use App\Livewire\Profile\Library\Anime\Index as AnimeLibrary;
use App\Livewire\Profile\Library\Anime\Reminders as AnimeReminders;
use App\Livewire\Profile\Library\Game\Favorites as FavoriteGame;
use App\Livewire\Profile\Library\Game\Index as GameLibrary;
use App\Livewire\Profile\Library\Manga\Favorites as FavoriteManga;
use App\Livewire\Profile\Library\Manga\Index as MangaLibrary;
use App\Livewire\Profile\Ratings\Index as RatingsIndex;

Route::prefix('/profile')
    ->name('profile')
    ->group(function () {
        Route::get('/', [MeController::class, 'index'])
            ->middleware(['auth'])
            ->name('.index');

        Route::get('/settings', [UserProfileController::class, 'settings'])
            ->middleware(['auth', 'verified'])
            ->name('.settings');

        Route::get('/{user}', Details::class)
            ->name('.details');

        Route::prefix('/{user}/anime')
            ->name('.anime')
            ->group(function () {
                Route::get('/', AnimeLibrary::class)
                    ->name('.library');

                Route::get('/favorites', FavoriteAnime::class)
                    ->name('.favorites');

                Route::get('/reminders', AnimeReminders::class)
                    ->name('.reminders');
            });

        Route::prefix('/{user}/games')
            ->name('.games')
            ->group(function () {
                Route::get('/', GameLibrary::class)
                    ->name('.library');

                Route::get('/favorites', FavoriteGame::class)
                    ->name('.favorites');

//                Route::get('/reminders', GameReminders::class)
//                    ->name('.reminders');
            });

        Route::prefix('/{user}/manga')
            ->name('.manga')
            ->group(function () {
                Route::get('/', MangaLibrary::class)
                    ->name('.library');

                Route::get('/favorites', FavoriteManga::class)
                    ->name('.favorites');

//                Route::get('/reminders', MangaReminders::class)
//                    ->name('.reminders');
            });

        Route::prefix('/{user}/ratings')
            ->name('.ratings')
            ->group(function () {
                Route::get('/', RatingsIndex::class)
                    ->name('.index');
            });
    });

 Route::get('/animelist/{user?}', [AnimeLibraryController::class, 'index'])
    ->name('animelist');

 Route::get('/mangalist/{user?}', [MangaLibraryController::class, 'index'])
    ->name('mangalist');

 Route::get('/gamelist/{user?}', [GameLibraryController::class, 'index'])
    ->name('gamelist');
