<?php

use App\Http\Controllers\Web\AnimeController;
use App\Http\Controllers\Web\MangaController;

Route::prefix('/{kitsu_url}')
    ->where(['kitsu_url' => '^(www\.)?kitsu(.io)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('kitsu')
    ->group(function () {
        Route::prefix('/anime')
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

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:slug}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [MangaController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [MangaController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
