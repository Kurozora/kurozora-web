<?php

use App\Http\Controllers\API\v1\AccessTokenController;

Route::prefix('/access-tokens')
    ->name('.access-tokens')
    ->group(function () {
        Route::get('/', [AccessTokenController::class, 'index'])
            ->name('.index');

        Route::prefix('{personalAccessToken}')
            ->group(function () {
                Route::get('/', [AccessTokenController::class, 'details'])
                    ->can('view', 'personalAccessToken')
                    ->name('.details');

                Route::post('/update', [AccessTokenController::class, 'update'])
                    ->can('update', 'personalAccessToken')
                    ->name('.update');

                Route::post('/delete', [AccessTokenController::class, 'delete'])
                    ->can('delete', 'personalAccessToken')
                    ->name('.delete');
            });
    });
