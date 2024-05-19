<?php

use App\Livewire\Person\Anime as PersonAnime;
use App\Livewire\Person\Characters as PersonCharacters;
use App\Livewire\Person\Details as PersonDetails;
use App\Livewire\Person\Games as PersonGames;
use App\Livewire\Person\Index as PersonIndex;
use App\Livewire\Person\Manga as PersonManga;
use App\Livewire\Person\Reviews as PersonReviews;

Route::prefix('/people')
    ->name('people')
    ->group(function () {
        Route::get('/', PersonIndex::class)
            ->name('.index');

        Route::prefix('{person}')
            ->group(function () {
                Route::get('/', PersonDetails::class)
                    ->name('.details');

                Route::get('/anime', PersonAnime::class)
                    ->name('.anime');

                Route::get('/characters', PersonCharacters::class)
                    ->name('.characters');

                Route::get('/games', PersonGames::class)
                    ->name('.games');

                Route::get('/manga', PersonManga::class)
                    ->name('.manga');

                Route::get('/reviews', PersonReviews::class)
                    ->name('.reviews');
            });
    });
