<?php

use App\Http\Controllers\ForumThreadController;

Route::prefix('/forum-threads')
    ->name('.forum-threads')
    ->group(function() {
        Route::get('/search', [ForumThreadController::class, 'search'])
            ->middleware('auth.kurozora:optional')
            ->name('.search');

        Route::get('/{thread}', [ForumThreadController::class, 'details'])
            ->middleware('auth.kurozora:optional')
            ->name('.details');

        Route::post('/{thread}/vote', [ForumThreadController::class, 'vote'])
            ->middleware('auth.kurozora')
            ->name('.vote');

        Route::get('/{thread}/replies', [ForumThreadController::class, 'replies'])
            ->middleware('auth.kurozora:optional')
            ->name('.replies');

        Route::post('/{thread}/replies', [ForumThreadController::class, 'postReply'])
            ->middleware('auth.kurozora')
            ->name('.reply');

        Route::post('/{thread}/lock', [ForumThreadController::class, 'lock'])
            ->middleware(['auth.kurozora', 'can:lock_thread,thread'])
            ->name('.lock');
    });
