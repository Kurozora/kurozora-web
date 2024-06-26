<?php

use App\Http\Controllers\API\v1\AccessTokenController;

Route::prefix('/access-tokens')
    ->name('.access-tokens')
    ->group(function () {
        Route::get('/', [AccessTokenController::class, 'index'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::prefix('{personalAccessToken}')
            ->group(function () {
                Route::get('/', [AccessTokenController::class, 'details'])
                    ->middleware(['auth.kurozora'])
                    ->can('view', 'personalAccessToken')
                    ->name('.details');

                Route::post('/update', [AccessTokenController::class, 'update'])
                    ->middleware(['auth.kurozora'])
                    ->can('update', 'personalAccessToken')
                    ->name('.update');

                Route::post('/delete', [AccessTokenController::class, 'delete'])
                    ->middleware(['auth.kurozora'])
                    ->can('delete', 'personalAccessToken')
                    ->name('.delete');
            });
    });
