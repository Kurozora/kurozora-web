<?php

use App\Livewire\KnowledgeBase\CommunityGuidelines;
use App\Livewire\KnowledgeBase\GeneratingDeveloperTokens;
use App\Livewire\KnowledgeBase\InAppPurchases;
use App\Livewire\KnowledgeBase\Personalization;

Route::prefix('/kb')
    ->name('kb')
    ->group(function() {
        Route::get('/generating-developer-tokens', GeneratingDeveloperTokens::class)
            ->name('.generating-developer-tokens');

        Route::get('/guidelines', CommunityGuidelines::class)
            ->name('.guidelines');

        Route::get('/iap', InAppPurchases::class)
            ->name('.iap');

        Route::get('/personalization', Personalization::class)
            ->name('.personalization');
    });
