<?php

use App\Livewire\Studio\Anime as StudioAnime;
use App\Livewire\Studio\Details as StudioDetails;
use App\Livewire\Studio\Games as StudioGames;
use App\Livewire\Studio\Index as StudioIndex;
use App\Livewire\Studio\Manga as StudioManga;
use App\Livewire\Studio\Reviews as StudioReviews;

Route::prefix('/studios')
    ->name('studios')
    ->group(function () {
        Route::get('/', StudioIndex::class)
            ->name('.index');

        Route::prefix('{studio}')
            ->group(function () {
                Route::get('/', StudioDetails::class)
                    ->name('.details');

                Route::get('/anime', StudioAnime::class)
                    ->name('.anime');

                Route::get('/manga', StudioManga::class)
                    ->name('.manga');

                Route::get('/games', StudioGames::class)
                    ->name('.games');

                Route::get('/reviews', StudioReviews::class)
                    ->name('.reviews');
            });
    });
