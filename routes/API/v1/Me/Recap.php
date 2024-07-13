<?php

use App\Http\Controllers\API\v1\RecapController;

Route::prefix('/recap')
    ->middleware('auth.kurozora')
    ->name('.recap')
    ->group(function () {
        Route::get('/', [RecapController::class, 'index'])
            ->name('.index');

        Route::prefix('{year}')
            ->where(['year' => '^\d{4}$'])
            ->group(function () {
                Route::get('/', [RecapController::class, 'view'])
                    ->name('.view');
            });
    });
