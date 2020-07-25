<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/genres')
    ->name('genres.')
    ->group(function() {
        Route::get('/', [GenreController::class, 'overview'])
            ->name('overview');

        Route::get('/{genre}', [GenreController::class, 'details'])
            ->name('details');
    });
