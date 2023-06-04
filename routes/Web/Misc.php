<?php

use App\Http\Livewire\Misc\Contact;
use App\Http\Livewire\Misc\PressKit;
use App\Http\Livewire\Misc\Projects;
use App\Http\Livewire\Misc\Team;

Route::name('misc')
    ->group(function() {
        Route::get('/team', Team::class)
            ->name('.team');

        Route::get('/projects', Projects::class)
            ->name('.projects');

        Route::get('/contact', Contact::class)
            ->name('.contact');

        Route::get('/press-kit', PressKit::class)
            ->name('.press-kit');
    });
