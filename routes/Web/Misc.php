<?php

use App\Livewire\Misc\Contact;
use App\Livewire\Misc\PressKit;
use App\Livewire\Misc\Projects;
use App\Livewire\Misc\Team;

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
