<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/anime-episodes')->group(function() {
    Route::post('/{episode}/watched', [AnimeEpisodeController::class, 'watched'])
        ->middleware('kurozora.userauth')
        ->middleware('anime.inLibrary');
});
