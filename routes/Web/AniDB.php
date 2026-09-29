<?php

use App\Http\Controllers\Web\AnimeController;

Route::prefix('/{anidb_url}')
    ->where(['anidb_url' => '^(www\.)?anidb(.net)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('anidb')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:anidb_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
