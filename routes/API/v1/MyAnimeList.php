<?php

use App\Http\Controllers\API\v1\AnimeController;
use App\Http\Controllers\API\v1\MangaController;

Route::prefix('/myanimelist')
    ->name('myanimelist')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:mal_id}')
                    ->group(function () {
                        Route::get('/', [AnimeController::class, 'view'])
                            ->middleware('auth.kurozora:optional')
                            ->name('.view');
                    });
            });

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:mal_id}')
                    ->group(function () {
                        Route::get('/', [MangaController::class, 'view'])
                            ->middleware('auth.kurozora:optional')
                            ->name('.view');
                    });
            });
    });
