<?php

use App\Http\Controllers\Web\KnowledgeBaseController;

Route::prefix('/kb')
    ->name('kb')
    ->group(function() {
        Route::get('/generating-developer-tokens', [KnowledgeBaseController::class, 'generatingDeveloperTokens'])
            ->name('.generating-developer-tokens');

        Route::get('/guidelines', [KnowledgeBaseController::class, 'guidelines'])
            ->name('.guidelines');

        Route::get('/iap', [KnowledgeBaseController::class, 'inAppPurchases'])
            ->name('.iap');

        Route::get('/personalization', [KnowledgeBaseController::class, 'personalization'])
            ->name('.personalization');
    });
