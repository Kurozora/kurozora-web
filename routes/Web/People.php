<?php

use App\Http\Livewire\Person\Anime as PersonAnime;
use App\Http\Livewire\Person\Details as PersonDetails;
use App\Http\Livewire\Person\Characters as PersonCharacters;

Route::prefix('/people')
    ->name('people')
    ->group(function () {
        Route::prefix('{person}')
            ->group(function () {
                Route::get('/', PersonDetails::class)
                    ->name('.details');

                Route::get('/anime', PersonAnime::class)
                    ->name('.anime');

                Route::get('/characters', PersonCharacters::class)
                    ->name('.characters');
            });
    });
