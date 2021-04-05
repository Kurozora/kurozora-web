<?php

namespace App\Http\Controllers\WebControllers;

use App\Http\Livewire\Legal\PrivacyPolicy;
use App\Http\Livewire\Legal\TermsOfUse;
use Illuminate\Support\Facades\Route;

Route::prefix('/legal')
    ->name('legal')
    ->group(function() {
        Route::get('/privacy', [PrivacyPageController::class, 'show'])
            ->name('privacy');
    });
