<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/forum-threads')->group(function() {
    Route::get('/search', [ForumThreadController::class, 'search'])
        ->middleware('kurozora.userauth:optional');

    Route::get('/{thread}', [ForumThreadController::class, 'threadInfo'])
        ->middleware('kurozora.userauth:optional');

    Route::post('/{thread}/vote', [ForumThreadController::class, 'vote'])
        ->middleware('kurozora.userauth');

    Route::get('/{thread}/replies', [ForumThreadController::class, 'replies'])
        ->middleware('kurozora.userauth:optional');

    Route::post('/{thread}/replies', [ForumThreadController::class, 'postReply'])
        ->middleware('kurozora.userauth');

    Route::post('/{thread}/lock', [ForumThreadController::class, 'lock'])
        ->middleware('kurozora.userauth')
        ->middleware('can:lock_thread,thread');
});
