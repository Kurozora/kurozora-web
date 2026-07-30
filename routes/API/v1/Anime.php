<?php

use App\Enums\UserLibraryKind;
use App\Http\Controllers\API\v1\AnimeController;
use App\Http\Controllers\API\v1\MuseumController;
use App\Http\Controllers\API\v1\ParentalGuideController;

Route::prefix('/anime')
    ->name('.anime')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [AnimeController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->name('.index');

        Route::get('/mapping', [AnimeController::class, 'mapping'])
            ->withoutMiddleware('cache.headers:private;no_cache;etag')
            ->name('.mapping');

        Route::get('/upcoming', [AnimeController::class, 'upcoming'])
            ->middleware('auth.kurozora:optional')
            ->name('.upcoming');

        Route::get('/seasons', [AnimeController::class, 'browseSeason'])
            ->middleware('auth.kurozora:optional')
            ->name('.browse-seasons');

        Route::get('/museum', [MuseumController::class, 'index'])
            ->middleware('auth.kurozora:optional')
            ->defaults('kind', UserLibraryKind::Anime)
            ->name('.museum');

        Route::get('/museum/{year}', [MuseumController::class, 'year'])
            ->middleware('auth.kurozora:optional')
            ->defaults('kind', UserLibraryKind::Anime)
            ->whereNumber('year')
            ->name('.museum.year');

        Route::prefix('{anime}')
            ->group(function () {
                Route::get('/', [AnimeController::class, 'view'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');

                Route::get('/characters', [AnimeController::class, 'characters'])
                    ->name('.characters');

                Route::get('/cast', [AnimeController::class, 'cast'])
                    ->name('.cast');

                Route::get('/related-shows', [AnimeController::class, 'relatedShows'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-shows');

                Route::get('/related-literatures', [AnimeController::class, 'relatedLiteratures'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-literatures');

                Route::get('/related-games', [AnimeController::class, 'relatedGames'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-games');

                Route::get('/seasons', [AnimeController::class, 'seasons'])
                    ->name('.seasons');

                Route::get('/songs', [AnimeController::class, 'songs'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.songs');

                Route::get('/staff', [AnimeController::class, 'staff'])
                    ->name('.staff');

                Route::get('/studios', [AnimeController::class, 'studios'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.studios');

                Route::get('/more-by-studio', [AnimeController::class, 'moreByStudio'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.more-by-studio');

                Route::prefix('rate')
                    ->middleware(['auth.kurozora', 'user.not-timed-out'])
                    ->group(function () {
                        Route::post('/', [AnimeController::class, 'rate'])
                            ->name('.rate');

                        Route::delete('/', [AnimeController::class, 'deleteRating'])
                            ->name('.delete-rating');
                    });

                Route::get('/reviews', [AnimeController::class, 'reviews'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.reviews');

                Route::prefix('parentalguide')
                    ->name('.parentalguide')
                    ->group(function () {
                        Route::get('/', [ParentalGuideController::class, 'indexForAnime'])
                            ->middleware('auth.kurozora:optional')
                            ->name('.index');

                        Route::post('/', [ParentalGuideController::class, 'storeForAnime'])
                            ->middleware(['auth.kurozora', 'user.not-timed-out'])
                            ->name('.store');
                    });
            });
    });
