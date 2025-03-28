<?php

use App\Livewire\AppIcon\Index as AppIconIndex;

Route::prefix('/app-icon')
    ->name('app-icon')
    ->group(function () {
        Route::get('/', AppIconIndex::class)
            ->name('.index');
    });
