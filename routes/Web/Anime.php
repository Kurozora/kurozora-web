<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime')
    ->name('anime.')
    ->group(function() {
        Route::get('/{anime}', [PageController::class, 'anime'])
        ->name('details');
    });
