<?php

use App\Http\Controllers\Web\MeController;
use App\Http\Controllers\Web\Profile\AnimeLibraryController;
use App\Http\Controllers\Web\Profile\GameLibraryController;
use App\Http\Controllers\Web\Profile\MangaLibraryController;
use App\Http\Controllers\Web\UserProfileController;
use App\Livewire\Profile\Badges\Index as AchievementsIndex;
use App\Livewire\Profile\Details;
use App\Livewire\Profile\Followers\Index as FollowersIndex;
use App\Livewire\Profile\Following\Index as FollowingIndex;
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

        Route::prefix('/{user}')
            ->group(function () {
                Route::get('/', Details::class)
                    ->name('.details');

                Route::prefix('/anime')
                    ->name('.anime')
                    ->group(function () {
                        Route::get('/', AnimeLibrary::class)
                            ->name('.library');

                        Route::get('/favorites', FavoriteAnime::class)
                            ->name('.favorites');

                        Route::get('/reminders', AnimeReminders::class)
                            ->name('.reminders');
                    });

                Route::prefix('/games')
                    ->name('.games')
                    ->group(function () {
                        Route::get('/', GameLibrary::class)
                            ->name('.library');

                        Route::get('/favorites', FavoriteGame::class)
                            ->name('.favorites');

//                        Route::get('/reminders', GameReminders::class)
//                            ->name('.reminders');
                    });

                Route::prefix('/manga')
                    ->name('.manga')
                    ->group(function () {
                        Route::get('/', MangaLibrary::class)
                            ->name('.library');

                        Route::get('/favorites', FavoriteManga::class)
                            ->name('.favorites');

//                        Route::get('/reminders', MangaReminders::class)
//                            ->name('.reminders');
                    });

                Route::get('/achievements', AchievementsIndex::class)
                    ->name('.achievements');

                Route::get('/followers', FollowersIndex::class)
                    ->name('.followers');

                Route::get('/following', FollowingIndex::class)
                    ->name('.following');

                Route::get('/ratings', RatingsIndex::class)
                    ->name('.ratings');
            });
    });

 Route::get('/animelist/{user?}', [AnimeLibraryController::class, 'index'])
    ->name('animelist');

 Route::get('/mangalist/{user?}', [MangaLibraryController::class, 'index'])
    ->name('mangalist');

 Route::get('/gamelist/{user?}', [GameLibraryController::class, 'index'])
    ->name('gamelist');
