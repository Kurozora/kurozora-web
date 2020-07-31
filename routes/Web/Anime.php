<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime')
    ->name('anime.')
    ->group(function() {
        Route::get('/{animeID}', [PageController::class, 'anime'])
        ->name('details');
    });
