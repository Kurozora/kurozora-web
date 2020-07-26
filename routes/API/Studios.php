<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/studios')
    ->name('studios.')
    ->group(function() {
        Route::get('/', [StudioController::class, 'overview'])
            ->name('overview');

        Route::get('/{studio}', [StudioController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('details');

        Route::get('/{studio}/anime', [StudioController::class, 'anime'])
            ->middleware('kurozora.userauth:optional')
            ->name('anime');
    });
