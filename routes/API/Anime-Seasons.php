<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime-seasons')->group(function() {
    Route::get('/{season}', [AnimeSeasonController::class, 'details']);

    Route::get('/{season}/episodes', [AnimeSeasonController::class, 'episodes'])
        ->middleware('kurozora.userauth:optional');
});
