<?php

use App\Livewire\UpNext\Episodes;

Route::prefix('/up-next')
    ->middleware(['auth'])
    ->name('up-next')
    ->group(function () {
        Route::get('/', function() {
            return to_route('up-next.episodes');
        })
            ->name('.index');

        Route::get('/episodes', Episodes::class)
            ->name('.episodes');
    });
