<?php

use App\Http\Livewire\Character\Anime as CharacterAnime;
use App\Http\Livewire\Character\Details as CharacterDetails;
use App\Http\Livewire\Character\Games as CharacterGames;
use App\Http\Livewire\Character\Index as CharacterIndex;
use App\Http\Livewire\Character\Manga as CharacterManga;
use App\Http\Livewire\Character\People as CharacterPeople;

Route::prefix('/characters')
    ->name('characters')
    ->group(function () {
        Route::get('/', CharacterIndex::class)
            ->name('.index');

        Route::prefix('{character}')
            ->group(function () {
                Route::get('/', CharacterDetails::class)
                    ->name('.details');

                Route::get('/anime', CharacterAnime::class)
                    ->name('.anime');

                Route::get('/games', CharacterGames::class)
                    ->name('.games');

                Route::get('/manga', CharacterManga::class)
                    ->name('.manga');

                Route::get('/people', CharacterPeople::class)
                    ->name('.people');
            });
    });
