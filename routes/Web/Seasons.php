<?php

use App\Http\Controllers\Web\SeasonController;
use App\Models\Season;

Route::prefix('/seasons')
    ->name('seasons')
    ->group(function () {
        Route::prefix('{season}')
            ->group(function () {
                Route::get('/edit', function (Season $season) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Season::uriKey() . '/' . $season->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/episodes', [SeasonController::class, 'episodes'])
                    ->name('.episodes');

                Route::get('/episodes/random', [SeasonController::class, 'randomEpisode'])
                    ->name('.episodes.random');
            });
    });
