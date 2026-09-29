<?php

use App\Http\Controllers\Web\AnimeController;

Route::prefix('/{livechart_url}')
    ->where(['livechart_url' => '^(www\.)?livechart(.me)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('livechart')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:livechart_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
