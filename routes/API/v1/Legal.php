<?php

use App\Http\Controllers\MiscController;

Route::prefix('/legal')
    ->name('.legal')
    ->group(function () {
        Route::get('privacy-policy', [MiscController::class, 'getPrivacyPolicy'])
            ->name('.privacy-policy');

        Route::get('terms-of-use', [MiscController::class, 'getTermsOfUse'])
            ->name('.terms-of-use');
    });
