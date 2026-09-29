<?php

use App\Http\Controllers\Web\AnimeController;
use App\Http\Controllers\Web\MangaController;

Route::prefix('/{anilist_url}')
    ->where(['anilist_url' => '^(www\.)?anilist(.co)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('anilist')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:anilist_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:anilist_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [MangaController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [MangaController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
