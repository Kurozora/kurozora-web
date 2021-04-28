<?php

use App\Http\Controllers\ForumThreadController;

Route::prefix('/forum-threads')
    ->name('.forum-threads')
    ->group(function() {
        Route::get('/search', [ForumThreadController::class, 'search'])
            ->middleware('kurozora.userauth:optional')
            ->name('.search');

        Route::get('/{thread}', [ForumThreadController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('.details');

        Route::post('/{thread}/vote', [ForumThreadController::class, 'vote'])
            ->middleware('kurozora.userauth')
            ->name('.vote');

        Route::get('/{thread}/replies', [ForumThreadController::class, 'replies'])
            ->middleware('kurozora.userauth:optional')
            ->name('.replies');

        Route::post('/{thread}/replies', [ForumThreadController::class, 'postReply'])
            ->middleware('kurozora.userauth')
            ->name('.reply');

        Route::post('/{thread}/lock', [ForumThreadController::class, 'lock'])
            ->middleware(['kurozora.userauth', 'can:lock_thread,thread'])
            ->name('.lock');
    });
