<?php

use App\Http\Livewire\Misc\Contact;

Route::name('misc')
    ->group(function() {
        Route::get('/contact', Contact::class)
            ->name('.contact');
    });
