<?php

use App\Http\Controllers\API\v1\FeedMessageController;

Route::prefix('/messages')
    ->name('.messages')
    ->group(function () {
        Route::get('/{feedMessage}', [FeedMessageController::class, 'details'])
            ->middleware('auth.kurozora:optional')
            ->name('.details');

        Route::post('/{feedMessage}/update', [FeedMessageController::class, 'update'])
            ->middleware('auth.kurozora')
            ->name('.update');

        Route::get('/{feedMessage}/replies', [FeedMessageController::class, 'replies'])
            ->middleware('auth.kurozora:optional')
            ->name('.replies');

        Route::post('/{feedMessage}/heart', [FeedMessageController::class, 'heart'])
            ->middleware('auth.kurozora')
            ->name('.heart');

        Route::post('/{feedMessage}/pin', [FeedMessageController::class, 'pin'])
            ->middleware('auth.kurozora')
            ->name('.pin');

        Route::post('/{feedMessage}/delete', [FeedMessageController::class, 'delete'])
            ->middleware('auth.kurozora')
            ->name('.delete');
    });
