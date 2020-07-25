<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime')->group(function() {
    Route::get('/{animeID}', [PageController::class, 'anime']);
});
