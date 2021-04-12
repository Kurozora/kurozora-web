<?php

use App\Http\Controllers\MeController;

Route::prefix('/feed-messages')
    ->name('.feed-messages')
    ->group(function() {
        Route::get('/', [MeController::class, 'getFeedMessages'])
            ->middleware('kurozora.userauth')
            ->name('.details');
    });
