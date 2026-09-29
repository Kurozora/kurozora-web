<?php

use App\Http\Controllers\Web\LegalController;

Route::prefix('/legal')
    ->name('legal')
    ->group(function () {
        Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])
            ->name('.privacy-policy');

        Route::get('/terms-of-use', [LegalController::class, 'termsOfUse'])
            ->name('.terms-of-use');
    });
