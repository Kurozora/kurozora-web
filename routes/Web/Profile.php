<?php

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\MeController;
use App\Http\Controllers\Web\Profile\LibraryController;
use App\Http\Controllers\Web\UserProfileController;
use App\Livewire\Profile\Achievements\Index as AchievementsIndex;
use App\Livewire\Profile\Blocked\Index as BlockedIndex;
use App\Livewire\Profile\Details;
use App\Livewire\Profile\Followers\Index as FollowersIndex;
use App\Livewire\Profile\Following\Index as FollowingIndex;
use App\Livewire\Profile\Library\Favorites;
use App\Livewire\Profile\Library\Index as LibraryIndex;
use App\Livewire\Profile\Library\Reminders;
use App\Livewire\Profile\Ratings\Index as RatingsIndex;

Route::prefix('/profile')
    ->name('profile')
    ->group(function () {
        Route::get('/', [MeController::class, 'index'])
            ->middleware(['auth'])
            ->name('.index');

        Route::prefix('/settings')
            ->middleware('auth')
            ->group(function () {
                Route::get('/', [UserProfileController::class, 'settings'])
                    ->name('.settings');

                Route::get('/{user}', [UserProfileController::class, 'settings'])
                    ->name('.settings.user');
            });

        Route::prefix('/{user}')
            ->middleware('can:view,user')
            ->group(function () {
                Route::get('/', Details::class)
                    ->name('.details');

                Route::prefix('/anime')
                    ->name('.anime')
                    ->group(function () {
                        Route::get('/', LibraryIndex::class)
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.library');

                        Route::get('/favorites', Favorites::class)
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.favorites');

                        Route::get('/reminders', Reminders::class)
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.reminders');
                    });

                Route::prefix('/games')
                    ->name('.games')
                    ->group(function () {
                        Route::get('/', LibraryIndex::class)
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.library');

                        Route::get('/favorites', Favorites::class)
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.favorites');

//                        Route::get('/reminders', Reminders::class)
//                            ->defaults('kind', UserLibraryKind::Game)
//                            ->name('.reminders');
                    });

                Route::prefix('/manga')
                    ->name('.manga')
                    ->group(function () {
                        Route::get('/', LibraryIndex::class)
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.library');

                        Route::get('/favorites', Favorites::class)
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.favorites');

//                        Route::get('/reminders', Reminders::class)
//                            ->defaults('kind', UserLibraryKind::Manga)
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

                Route::get('/blocked', BlockedIndex::class)
                    ->middleware('auth')
                    ->name('.blocked');
            });
    });

 Route::get('/animelist/{user?}', [LibraryController::class, 'index'])
    ->defaults('kind', UserLibraryKind::Anime)
    ->name('animelist');

 Route::get('/mangalist/{user?}', [LibraryController::class, 'index'])
    ->defaults('kind', UserLibraryKind::Manga)
    ->name('mangalist');

 Route::get('/gamelist/{user?}', [LibraryController::class, 'index'])
    ->defaults('kind', UserLibraryKind::Game)
    ->name('gamelist');
