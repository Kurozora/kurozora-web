<?php

use App\Http\Controllers\Web\AnimeController;

Route::prefix('/{tvdb_url}')
    ->where(['tvdb_url' => '^(www\.)?(thetvdb|tvdb)(.com)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('tvdb')
    ->group(function () {
        Route::prefix('/series')
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
