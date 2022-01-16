<?php

use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\MeController;

Route::prefix('/access-tokens')
    ->name('.access-tokens')
    ->group(function () {
        Route::get('/', [MeController::class, 'getAccessTokens'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::prefix('{accessToken}')
            ->group(function () {
                Route::get('/', [AccessTokenController::class, 'details'])
                    ->middleware('auth.kurozora')
                    ->middleware('can:get_accessToken,accessToken')
                    ->name('.details');

                Route::post('/update', [AccessTokenController::class, 'update'])
                    ->middleware('auth.kurozora')
                    ->middleware('can:update_accessToken,accessToken')
                    ->name('.update');

                Route::post('/delete', [AccessTokenController::class, 'delete'])
                    ->middleware(['auth.kurozora', 'can:delete_accessToken,accessToken'])
                    ->name('.delete');
            });
    });
