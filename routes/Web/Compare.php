<?php

use App\Http\Controllers\Web\CompareController;

Route::prefix('/compare')
    ->name('compare')
    ->group(function () {
        Route::get('/', [CompareController::class, 'index'])
            ->name('.index');
    });
