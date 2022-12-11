<?php

use App\Http\Controllers\API\v1\AccessTokenController;
use App\Http\Controllers\API\v1\MeController;

Route::prefix('/access-tokens')
    ->name('.access-tokens')
    ->group(function () {
        Route::get('/', [MeController::class, 'getAccessTokens'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::prefix('{personalAccessToken}')
            ->group(function () {
                Route::get('/', [AccessTokenController::class, 'details'])
                    ->middleware(['auth.kurozora'])
                    ->can('get_access_token', 'personalAccessToken')
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
