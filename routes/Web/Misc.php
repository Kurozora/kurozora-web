<?php

use App\Http\Livewire\Misc\Contact;
use App\Http\Livewire\Misc\PressKit;
use App\Http\Livewire\Misc\Team;

Route::name('misc')
    ->group(function() {
        Route::get('/contact', Contact::class)
            ->name('.contact');

        Route::get('/press-kit', PressKit::class)
            ->name('.press-kit');

        Route::get('/team', Team::class)
            ->name('.team');
    });
