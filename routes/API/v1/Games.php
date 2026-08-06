<?php

use App\Enums\UserLibraryKind;
use App\Http\Controllers\API\v1\GameController;
use App\Http\Controllers\API\v1\MuseumController;
use App\Http\Controllers\API\v1\ParentalGuideController;

Route::prefix('/games')
    ->name('.games')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [GameController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::get('/upcoming', [GameController::class, 'upcoming'])
            ->middleware('auth.kurozora:optional')
            ->name('.upcoming');

        Route::get('/seasons', [GameController::class, 'browseSeason'])
            ->middleware('auth.kurozora:optional')
            ->name('.seasons');

        Route::get('/museum', [MuseumController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.museum');

        Route::get('/museum/{year}', [MuseumController::class, 'year'])
            ->middleware('auth.kurozora:optional')
            ->defaults('kind', UserLibraryKind::Game)
            ->whereNumber('year')
            ->name('.museum.year');

        Route::prefix('{game}')
            ->group(function () {
                Route::get('/', [GameController::class, 'view'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');

                Route::get('/characters', [GameController::class, 'characters'])
                    ->name('.characters');

                Route::get('/cast', [GameController::class, 'cast'])
                    ->name('.cast');

                Route::get('/related-shows', [GameController::class, 'relatedShows'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-shows');

                Route::get('/related-literatures', [GameController::class, 'relatedLiteratures'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-literatures');

                Route::get('/related-games', [GameController::class, 'relatedGames'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-games');

                Route::get('/songs', [GameController::class, 'songs'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.songs');

                Route::get('/staff', [GameController::class, 'staff'])
                    ->name('.staff');

                Route::get('/studios', [GameController::class, 'studios'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.studios');

                Route::get('/more-by-studio', [GameController::class, 'moreByStudio'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.more-by-studio');

                Route::post('/rate', [GameController::class, 'rate'])
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->name('.rate');

                Route::get('/reviews', [GameController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');

                Route::prefix('parentalguide')
                    ->name('.parentalguide')
                    ->group(function () {
                        Route::get('/', [ParentalGuideController::class, 'indexForGame'])
                            ->middleware('auth.kurozora:optional')
                            ->name('.index');

                        Route::post('/', [ParentalGuideController::class, 'storeForGame'])
                            ->middleware(['auth.kurozora', 'user.not-timed-out'])
                            ->name('.store');
                    });
            });
    });
