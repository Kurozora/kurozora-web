<?php

use App\Http\Controllers\Web\DigestController;

Route::prefix('/digest')
    ->name('digest')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [DigestController::class, 'index'])
            ->name('.index');
    });
