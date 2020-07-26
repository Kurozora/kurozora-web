<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/characters')
    ->name('characters.')
    ->group(function() {
        Route::get('/', [CharacterController::class, 'overview'])
            ->name('overview');

        Route::get('/{character}', [CharacterController::class, 'details'])
            ->name('details');
    });
