<?php

use App\Http\Controllers\Web\SearchSuggestionsController;
use App\Livewire\Search\Index as SearchIndex;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;

Route::prefix('/search')
    ->name('search')
    ->group(function () {
        Route::get('/', SearchIndex::class)
            ->name('.index');

        Route::get('/suggestions', [SearchSuggestionsController::class, 'index'])
            ->name('.suggestions');
    });

Route::prefix('/random')
    ->name('random')
    ->group(function () {
        Route::get('/anime', function () {
            return to_route('anime.details', Anime::randomFirst());
        })
            ->name('.anime');

        Route::get('/manga', function () {
            return to_route('manga.details', Manga::randomFirst());
        })
            ->name('.manga');

        Route::get('/games', function () {
            return to_route('games.details', Game::randomFirst());
        })
            ->name('.games');
    });
