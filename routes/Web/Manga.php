<?php

use App\Http\Livewire\Manga\Cast as MangaCast;
use App\Http\Livewire\Manga\Details as MangaDetails;
use App\Http\Livewire\Manga\Index as MangaIndex;
use App\Http\Livewire\Manga\RelatedMangas;
use App\Http\Livewire\Manga\RelatedShows;
use App\Http\Livewire\Manga\Staff as MangaStaff;
use App\Http\Livewire\Manga\Studios as MangaStudios;

Route::prefix('/manga')
    ->name('manga')
    ->group(function () {
        Route::get('/', MangaIndex::class)
            ->name('.index');

        Route::prefix('{manga}')
            ->group(function () {
                Route::get('/', MangaDetails::class)
                    ->name('.details');

                Route::get('/cast', MangaCast::class)
                    ->name('.cast');

                Route::get('/staff', MangaStaff::class)
                    ->name('.staff');

                Route::get('/studios', MangaStudios::class)
                    ->name('.studios');

                Route::get('/related-shows', RelatedShows::class)
                    ->name('.related-shows');

                Route::get('/related-mangas', RelatedMangas::class)
                    ->name('.related-mangas');
            });
    });
