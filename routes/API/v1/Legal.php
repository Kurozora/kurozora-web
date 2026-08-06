<?php

use App\Http\Controllers\API\v1\MiscController;

Route::prefix('/legal')
    ->name('.legal')
    ->middleware('cache.headers:private;max_age=3600;etag')
    ->group(function () {
        Route::get('privacy-policy', [MiscController::class, 'getPrivacyPolicy'])
            ->name('.privacy-policy');

        Route::get('terms-of-use', [MiscController::class, 'getTermsOfUse'])
            ->name('.terms-of-use');
    });
