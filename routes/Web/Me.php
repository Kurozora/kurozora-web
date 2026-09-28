<?php

use App\Http\Controllers\Web\MeController;

Route::prefix('/me')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [MeController::class, 'index'])
            ->name('me');

        Route::get('/navigation', function () {
            return view('navigation');
        })
            ->name('me.navigation');
    });
