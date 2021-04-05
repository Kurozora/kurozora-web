<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/legal')
    ->name('.legal')
    ->group(function() {
        Route::get('privacy-policy', [MiscController::class, 'getPrivacyPolicy'])
            ->name('privacy');
    });
