<?php

use App\Http\Controllers\Web\AppIconController;

Route::prefix('/app-icons')
    ->name('app-icons')
    ->group(function () {
        Route::get('/', [AppIconController::class, 'index'])
            ->name('.index');
    });
