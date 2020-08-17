<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/sessions')
    ->name('sessions.')
    ->group(function() {
        Route::get('/', [MeController::class, 'getSessions'])
            ->middleware('kurozora.userauth')
            ->name('index');
    });
