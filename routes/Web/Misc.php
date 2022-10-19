<?php

use App\Http\Livewire\Misc\Contact;
use App\Http\Livewire\Misc\Creators;
use App\Http\Livewire\Misc\PressKit;

Route::name('misc')
    ->group(function() {
        Route::get('/contact', Contact::class)
            ->name('.contact');

        Route::get('/creators', Creators::class)
            ->name('.creators');

        Route::get('/press-kit', PressKit::class)
            ->name('.press-kit');
    });
