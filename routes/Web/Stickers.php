<?php

use App\Livewire\Stickers\Index as StickersIndex;

Route::prefix('/stickers')
    ->name('stickers')
    ->middleware('cache.headers:public;max_age=3600;etag')
    ->group(function () {
        Route::get('/', StickersIndex::class)
            ->name('.index');
    });
