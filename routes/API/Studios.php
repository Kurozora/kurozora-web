<?php

use App\Http\Controllers\StudioController;

Route::prefix('/studios')
    ->name('.studios')
    ->group(function() {
        Route::get('/{studio}', [StudioController::class, 'details'])
            ->middleware('auth.kurozora:optional')
            ->name('.details');

        Route::get('/{studio}/anime', [StudioController::class, 'anime'])
            ->middleware('auth.kurozora:optional')
            ->name('.anime');
    });
