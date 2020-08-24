<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/messages')
    ->name('messages.')
    ->group(function() {
        Route::get('/{feedMessage}', [FeedMessageController::class, 'details'])
            ->middleware('kurozora.userauth:optional')
            ->name('details');

        Route::post('/{feedMessage}/heart', [FeedMessageController::class, 'heart'])
            ->middleware('kurozora.userauth');
    });
