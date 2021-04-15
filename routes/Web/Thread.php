<?php

use App\Http\Livewire\Thread\Details;

Route::prefix('/thread')
    ->name('thread')
    ->group(function() {
        Route::get('/{thread}', Details::class)
            ->name('.details');
    });
