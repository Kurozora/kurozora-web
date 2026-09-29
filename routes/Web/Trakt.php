<?php

use App\Http\Controllers\Web\AnimeController;

Route::prefix('/{trakt_url}')
    ->where(['trakt_url' => '^(www\.)?trakt(.tv)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('trakt')
    ->group(function () {
        Route::prefix('/shows')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:slug}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
