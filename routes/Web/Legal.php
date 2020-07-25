<?php

namespace App\Http\Controllers\WebControllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/legal')->name('legal.')->group(function() {
    Route::get('/privacy', [PrivacyPageController::class, 'show'])
        ->name('privacy');
});
