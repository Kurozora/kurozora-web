<?php

use App\Livewire\AppIcon\Index as AppIconIndex;

Route::prefix('/app-icons')
    ->name('app-icons')
    ->group(function () {
        Route::get('/', AppIconIndex::class)
            ->name('.index');
    });
