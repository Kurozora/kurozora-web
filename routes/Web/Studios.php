<?php

use App\Http\Livewire\Studio\Anime as StudioAnime;
use App\Http\Livewire\Studio\Details as StudioDetails;

Route::prefix('/studios')
    ->name('studios')
    ->group(function() {
        Route::prefix('{studio}')
            ->group(function () {
                Route::get('/', StudioDetails::class)
                    ->name('.details');

                Route::get('/anime', StudioAnime::class)
                    ->name('.anime');
            });
    });
