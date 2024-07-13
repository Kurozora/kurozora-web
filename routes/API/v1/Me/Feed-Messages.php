<?php

use App\Http\Controllers\API\v1\MeController;

Route::prefix('/feed-messages')
    ->middleware('auth.kurozora')
    ->name('.feed-messages')
    ->group(function () {
        Route::get('/', [MeController::class, 'getFeedMessages'])
            ->name('.details');
    });
