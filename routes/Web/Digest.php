<?php

use App\Livewire\Digest\Index as DigestIndex;

Route::prefix('/digest')
    ->name('digest')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', DigestIndex::class)
            ->name('.index');
    });
