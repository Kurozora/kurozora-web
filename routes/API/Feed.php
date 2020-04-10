<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/feed')->group(function() {
    Route::post('/', [PostToFeedController::class, 'post'])
        ->middleware('kurozora.userauth');

    Route::get('/personal', [FeedController::class, 'personal'])
        ->middleware('kurozora.userauth');

    Route::get('/global', [FeedController::class, 'global'])
        ->middleware('kurozora.userauth:optional');
});
