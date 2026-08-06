<?php

use App\Http\Controllers\API\v1\GameCastController;
use App\Http\Controllers\API\v1\LiteratureCastController;
use App\Http\Controllers\API\v1\ShowCastController;

Route::prefix('/cast')
    ->name('.cast')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [ShowCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/show-cast')
    ->name('.show-cast')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [ShowCastController::class, 'index'])
            ->name('.index');

        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [ShowCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/literature-cast')
    ->name('.literature-cast')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [LiteratureCastController::class, 'index'])
            ->name('.index');

        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [LiteratureCastController::class, 'details'])
                    ->name('.details');
            });
    });

Route::prefix('/game-cast')
    ->name('.game-cast')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [GameCastController::class, 'index'])
            ->name('.index');

        Route::prefix('{cast}')
            ->group(function () {
                Route::get('/', [GameCastController::class, 'details'])
                    ->name('.details');
            });
    });
