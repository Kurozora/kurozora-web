<?php

use App\Http\Controllers\Nova\PresenceSeedController;

Route::prefix('/admin')
    ->middleware(['auth', 'can:viewNova'])
    ->group(function () {
        Route::prefix('/presence')
            ->name('admin.presence')
            ->group(function () {
                Route::get('/seed', [PresenceSeedController::class, 'show'])
                    ->name('.seed');
            });
    });
