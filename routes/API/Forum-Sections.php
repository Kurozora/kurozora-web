<?php

use App\Http\Controllers\ForumSectionController;

Route::prefix('/forum-sections')
    ->name('.forum-sections')
    ->group(function() {
        Route::get('/', [ForumSectionController::class, 'overview'])
            ->name('.overview');

        Route::get('/{section}', [ForumSectionController::class, 'details'])
            ->name('.details');

        Route::get('/{section}/threads', [ForumSectionController::class, 'threads'])
            ->middleware('auth.kurozora:optional')
            ->name('.threads');

        Route::post('/{section}/threads', [ForumSectionController::class, 'postThread'])
            ->middleware('auth.kurozora');
    });
