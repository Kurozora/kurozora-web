<?php

use App\Http\Controllers\Web\AnimeController;
use App\Http\Controllers\Web\MangaController;

Route::prefix('/{anisearch_url}')
    ->where(['anisearch_url' => '^(www\.)?anisearch(.com)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('anisearch')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:anisearch_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get(',{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:anisearch_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [MangaController::class, 'show'])
                            ->name('.index');

                        Route::get(',{any}', [MangaController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
