<?php

use App\Http\Controllers\Web\AnimeController;

Route::prefix('/{notify_url}')
    ->where(['notify_url' => '^(www\.)?notify(.moe)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('notify')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:notify_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
