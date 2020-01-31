<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/forum-sections')->group(function() {
    Route::get('/', [ForumSectionController::class, 'overview']);

    Route::get('/{section}', [ForumSectionController::class, 'details']);

    Route::get('/{section}/threads', [ForumSectionController::class, 'threads'])
        ->middleware('kurozora.userauth:optional');

    Route::post('/{section}/threads', [ForumSectionController::class, 'postThread'])
        ->middleware('kurozora.userauth');
});
