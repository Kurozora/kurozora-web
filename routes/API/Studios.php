<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/studios')->group(function() {
    Route::get('/', [StudioController::class, 'overview'])
        ->middleware('kurozora.userauth:optional');;

    Route::get('/{studio}', [StudioController::class, 'details'])
        ->middleware('kurozora.userauth:optional');;
});
