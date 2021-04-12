<?php

use App\Http\Livewire\Profile\Details;

Route::prefix('/profile')
    ->name('profile')
    ->group(function() {
        Route::get('/{user}', Details::class)
            ->name('.details');
    });
