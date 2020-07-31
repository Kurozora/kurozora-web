<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/profile')
    ->name('profile.')
    ->group(function() {
        Route::get('/{userID}', [PageController::class, 'userProfile'])
        ->name('details');
    });
