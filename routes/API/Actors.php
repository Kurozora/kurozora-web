<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/actors')->group(function() {
    Route::get('/', [ActorController::class, 'overview']);

    Route::get('/{actor}', [ActorController::class, 'details']);
});
