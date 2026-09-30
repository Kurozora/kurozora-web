<?php

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\MeController;
use App\Http\Controllers\Web\Profile\AchievementController;
use App\Http\Controllers\Web\Profile\BlockedController;
use App\Http\Controllers\Web\Profile\FavoriteController;
use App\Http\Controllers\Web\Profile\FollowerController;
use App\Http\Controllers\Web\Profile\FollowingController;
use App\Http\Controllers\Web\Profile\LibraryController;
use App\Http\Controllers\Web\Profile\RatingController;
use App\Http\Controllers\Web\Profile\ReminderController;
use App\Http\Controllers\Web\Profile\SessionController;
use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\UserProfileController;

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

                Route::get('/sessions', [SessionController::class, 'index'])
                    ->name('.settings.sessions');

                Route::get('/{user}', [UserProfileController::class, 'settings'])
                    ->name('.settings.user');
            });

        Route::prefix('/{user}')
            ->middleware('can:view,user')
            ->group(function () {
                Route::get('/', [UserProfileController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'profile'])
                    ->where('section', 'banner-image|profile-image|anime-library|manga-library|games-library|anime-favorites|manga-favorites|games-favorites|feed-messages')
                    ->middleware('auth')
                    ->name('.section');

                Route::prefix('/anime')
                    ->name('.anime')
                    ->group(function () {
                        Route::get('/', [LibraryController::class, 'show'])
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.library');

                        Route::get('/random', [LibraryController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.library.random');

                        Route::get('/favorites', [FavoriteController::class, 'index'])
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.favorites');

                        Route::get('/favorites/random', [FavoriteController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.favorites.random');

                        Route::get('/reminders', [ReminderController::class, 'index'])
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.reminders');

                        Route::get('/reminders/random', [ReminderController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.reminders.random');
                    });

                Route::prefix('/games')
                    ->name('.games')
                    ->group(function () {
                        Route::get('/', [LibraryController::class, 'show'])
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.library');

                        Route::get('/random', [LibraryController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.library.random');

                        Route::get('/favorites', [FavoriteController::class, 'index'])
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.favorites');

                        Route::get('/favorites/random', [FavoriteController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.favorites.random');

//                        Route::get('/reminders', Reminders::class)
//                            ->defaults('kind', UserLibraryKind::Game)
//                            ->name('.reminders');
                    });

                Route::prefix('/manga')
                    ->name('.manga')
                    ->group(function () {
                        Route::get('/', [LibraryController::class, 'show'])
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.library');

                        Route::get('/random', [LibraryController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.library.random');

                        Route::get('/favorites', [FavoriteController::class, 'index'])
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.favorites');

                        Route::get('/favorites/random', [FavoriteController::class, 'random'])
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.favorites.random');

//                        Route::get('/reminders', Reminders::class)
//                            ->defaults('kind', UserLibraryKind::Manga)
//                            ->name('.reminders');
                    });

                Route::get('/achievements', [AchievementController::class, 'index'])
                    ->name('.achievements');

                Route::get('/followers', [FollowerController::class, 'index'])
                    ->name('.followers');

                Route::get('/following', [FollowingController::class, 'index'])
                    ->name('.following');

                Route::get('/ratings', [RatingController::class, 'index'])
                    ->name('.ratings');

                Route::get('/blocked', [BlockedController::class, 'index'])
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
