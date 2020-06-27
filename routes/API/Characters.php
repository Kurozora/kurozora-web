<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/characters')->group(function() {
    Route::get('/', [CharacterController::class, 'overview']);

    Route::get('/{character}', [CharacterController::class, 'details']);
});
