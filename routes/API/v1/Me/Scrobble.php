<?php

use App\Http\Controllers\API\v1\ScrobbleController;

Route::prefix('/scrobble')
    ->middleware(['auth.kurozora', 'ability:scrobble', 'throttle:api.scrobble'])
    ->name('.scrobble')
    ->group(function () {
        Route::post('/start', [ScrobbleController::class, 'start'])
            ->name('.start');

        Route::post('/pause', [ScrobbleController::class, 'pause'])
            ->name('.pause');

        Route::post('/stop', [ScrobbleController::class, 'stop'])
            ->name('.stop');

        Route::post('/history', [ScrobbleController::class, 'history'])
            ->name('.history');

        Route::delete('/', [ScrobbleController::class, 'cancel'])
            ->name('.cancel');
    });
