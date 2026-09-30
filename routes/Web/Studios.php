<?php

use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\StudioController;
use App\Models\Studio;

Route::prefix('/studios')
    ->name('studios')
    ->group(function () {
        Route::get('/', [StudioController::class, 'index'])
            ->name('.index');

        Route::get('/random', [StudioController::class, 'random'])
            ->name('.random');

        Route::prefix('{studio}')
            ->group(function () {
                Route::get('/', [StudioController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'studio'])
                    ->where('section', 'anime|manga|games|reviews')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/edit', function (Studio $studio) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Studio::uriKey() . '/' . $studio->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/anime', [StudioController::class, 'anime'])
                    ->name('.anime');

                Route::get('/games', [StudioController::class, 'games'])
                    ->name('.games');

                Route::get('/manga', [StudioController::class, 'manga'])
                    ->name('.manga');

                Route::get('/reviews', [ReviewController::class, 'studio'])
                    ->name('.reviews');
            });
    });
