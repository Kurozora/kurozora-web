<?php

use App\Http\Controllers\Web\RecapController;

Route::prefix('/recap')
    ->name('recap')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', [RecapController::class, 'index'])
            ->name('.index');
    });
