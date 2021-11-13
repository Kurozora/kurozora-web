<?php

use App\Http\Livewire\Misc\Contact;
use App\Http\Livewire\Misc\Creators;

Route::name('misc')
    ->group(function() {
        Route::get('/contact', Contact::class)
            ->name('.contact');

        Route::get('/creators', Creators::class)
            ->name('.creators');
    });
