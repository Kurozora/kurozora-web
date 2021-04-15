<?php

use App\Http\Livewire\Legal\PrivacyPolicy;
use App\Http\Livewire\Legal\TermsOfUse;

Route::prefix('/legal')
    ->name('legal')
    ->group(function() {
        Route::get('/privacy-policy', PrivacyPolicy::class)
            ->name('.privacy-policy');

        Route::get('/terms-of-use', TermsOfUse::class)
            ->name('.terms-of-use');
    });
