<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/studios')->group(function() {
    Route::get('/', [StudioController::class, 'overview']);

    Route::get('/{studio}', [StudioController::class, 'details']);
});
