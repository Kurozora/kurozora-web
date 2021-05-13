<?php

use App\Http\Controllers\ForumReplyController;

Route::prefix('/forum-replies')
    ->name('.forum-replies')
    ->group(function() {
        Route::post('/{reply}/vote', [ForumReplyController::class, 'vote'])
            ->middleware('auth.kurozora');
    });
