<?php

use App\Http\Controllers\FeedMessageController;

Route::prefix('/messages')
    ->name('.messages')
    ->group(function() {
        Route::get('/{feedMessage}', [FeedMessageController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('.details');

        Route::get('/{feedMessage}/replies', [FeedMessageController::class, 'replies'])
            ->middleware('kurozora.userauth:optional')
            ->name('.replies');

        Route::post('/{feedMessage}/heart', [FeedMessageController::class, 'heart'])
            ->middleware('kurozora.userauth')
            ->name('.heart');
    });
