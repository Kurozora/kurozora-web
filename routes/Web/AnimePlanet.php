<?php

use App\Http\Controllers\Web\AnimeController;
use App\Http\Controllers\Web\MangaController;

Route::prefix('/{anime_planet_url}')
    ->where(['anime_planet_url' => '^(www\.)?anime(-)?planet.com'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('animeplanet')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:animeplanet_id}')
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
                Route::prefix('{manga:animeplanet_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [MangaController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [MangaController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
