<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/actors')
    ->name('actors.')
    ->group(function() {
        Route::get('/', [ActorController::class, 'overview'])
            ->name('overview');

        Route::get('/{actor}', [ActorController::class, 'details'])
            ->name('details');
    });
