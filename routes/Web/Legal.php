<?php

use App\Livewire\Legal\PrivacyPolicy;
use App\Livewire\Legal\TermsOfUse;

Route::prefix('/legal')
    ->name('legal')
    ->group(function () {
        Route::get('/privacy-policy', PrivacyPolicy::class)
            ->name('.privacy-policy');

        Route::get('/terms-of-use', TermsOfUse::class)
            ->name('.terms-of-use');
    });
