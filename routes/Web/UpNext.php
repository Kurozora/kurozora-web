<?php

use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\UpNextController;

Route::prefix('/up-next')
    ->middleware(['auth'])
    ->name('up-next')
    ->group(function () {
        Route::get('/', function() {
            return to_route('up-next.episodes');
        })
            ->name('.index');

        Route::get('/episodes', [UpNextController::class, 'episodes'])
            ->name('.episodes');

        Route::get('/section/{section}', [SectionController::class, 'upNext'])
            ->where('section', 'episodes|past-episodes')
            ->name('.section');
    });
