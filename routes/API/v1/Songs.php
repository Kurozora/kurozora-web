<?php

use App\Http\Controllers\SongController;

Route::prefix('/songs')
    ->name('.songs')
    ->group(function () {
        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', [SongController::class, 'details'])
                    ->name('.details');

                Route::get('/anime', [SongController::class, 'anime'])
                    ->name('.anime');
            });
    });
