<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/feed-messages')
    ->name('.feed-messages')
    ->group(function() {
        Route::get('/', [MeController::class, 'getFeedMessages'])
            ->middleware('kurozora.userauth')
            ->name('.details');
    });
