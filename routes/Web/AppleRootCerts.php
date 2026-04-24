<?php

use App\Http\Controllers\Web\AppleRootCertsController;

Route::prefix('/admin/apple-root-certs')
    ->middleware(['auth'])
    ->name('admin.apple-root-certs')
    ->group(function () {
        Route::post('/refresh', [AppleRootCertsController::class, 'refresh'])
            ->name('.refresh');
    });
