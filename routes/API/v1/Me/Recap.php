<?php

use App\Http\Controllers\API\v1\RecapController;

Route::prefix('/recap')
    ->middleware('auth.kurozora')
    ->name('.recap')
    ->group(function () {
        Route::get('/', [RecapController::class, 'index'])
            ->name('.index');

        // MARK: - Remove after 1.11.0
        Route::prefix('{year}')
            ->where(['year' => '^\d{4}$'])
            ->group(function () {
                Route::get('/', [RecapController::class, 'oldView'])
                    ->name('.old-view');
            });

        Route::prefix('{year}/{month}')
            ->where(['year' => '^\d{4}$', 'month' => '^(0?[1-9]|1[012])$'])
            ->group(function () {
                Route::get('/', [RecapController::class, 'view'])
                    ->name('.view');
            });
    });
