<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/thread')->group(function() {
    Route::get('/{threadID}', [PageController::class, 'thread']);
});
