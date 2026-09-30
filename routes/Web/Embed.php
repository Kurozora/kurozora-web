<?php

use App\Http\Controllers\Web\EmbedController;
use App\Http\Controllers\Web\OEmbedController;

Route::prefix('/oembed')
    ->name('oembed')
    ->middleware(['headers.http-accept:json'])
    ->group(function () {
        Route::get('/', [OEmbedController::class, 'show']);
    });

Route::prefix('/embed')
    ->name('embed')
    ->middleware(['headers.http-csp'])
    ->group(function () {
        Route::prefix('/episodes/{episode}')
            ->group(function () {
                Route::get('/', [EmbedController::class, 'episode'])
                    ->name('.episodes');
            });

        Route::prefix('/songs/{song}')
            ->group(function () {
                Route::get('/', [EmbedController::class, 'song'])
                    ->name('.songs');
            });
    });

