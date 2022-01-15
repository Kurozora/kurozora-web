<?php

use App\Http\Livewire\KnowledgeBase\InAppPurchases;
use App\Http\Livewire\KnowledgeBase\Personalisation;

Route::prefix('/kb')
    ->name('kb')
    ->group(function() {
        Route::get('/iap', InAppPurchases::class)
            ->name('.iap');

        Route::get('/personalisation', Personalisation::class)
            ->name('.personalisation');
    });
