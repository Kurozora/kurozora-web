<?php

use App\Http\Controllers\API\v1\MangaController;

Route::prefix('/manga')
    ->name('.manga')
    ->group(function () {
        Route::get('/upcoming', [MangaController::class, 'upcoming'])
            ->middleware('auth.kurozora:optional')
            ->name('.upcoming');

        Route::prefix('{manga}')
            ->group(function () {
                Route::get('/', [MangaController::class, 'view'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');

                Route::get('/characters', [MangaController::class, 'characters'])
                    ->name('.characters');

                Route::get('/cast', [MangaController::class, 'cast'])
                    ->name('.cast');

                Route::get('/related-shows', [MangaController::class, 'relatedShows'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-shows');

                Route::get('/related-mangas', [MangaController::class, 'relatedMangas'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.related-mangas');

                Route::get('/seasons', [MangaController::class, 'seasons'])
                    ->name('.seasons');

                Route::get('/songs', [MangaController::class, 'songs'])
                    ->name('.songs');

                Route::get('/staff', [MangaController::class, 'staff'])
                    ->name('.staff');

                Route::get('/studios', [MangaController::class, 'studios'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.studios');

                Route::get('/more-by-studio', [MangaController::class, 'moreByStudio'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.more-by-studio');

                Route::post('/rate', [MangaController::class, 'rateManga'])
                    ->middleware('auth.kurozora')
                ->name('.rate');
            });
    });
