<?php

use App\Http\Controllers\FeedMessageController;

Route::prefix('/messages')
    ->name('.messages')
    ->group(function () {
        Route::get('/{feedMessage}', [FeedMessageController::class, 'details'])
            ->middleware('auth.kurozora:optional')
            ->name('.details');

        Route::get('/{feedMessage}/replies', [FeedMessageController::class, 'replies'])
            ->middleware('auth.kurozora:optional')
            ->name('.replies');

        Route::post('/{feedMessage}/heart', [FeedMessageController::class, 'heart'])
            ->middleware('auth.kurozora')
            ->name('.heart');
    });
