<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/forum-replies')
    ->name('.forum-replies')
    ->group(function() {
        Route::post('/{reply}/vote', [ForumReplyController::class, 'vote'])
            ->middleware('kurozora.userauth');
    });
