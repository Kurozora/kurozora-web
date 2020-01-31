<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/genres')->group(function() {
    Route::get('/', [GenreController::class, 'overview']);

    Route::get('/{genre}', [GenreController::class, 'details']);
});
