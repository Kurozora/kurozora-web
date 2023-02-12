<?php

use App\Http\Livewire\Browse\Manga\Continuing\Index as BrowseMangaContinuingIndex;
use App\Http\Livewire\Browse\Manga\Seasons\Archive as BrowseMangaSeasonsArchive;
use App\Http\Livewire\Browse\Manga\Seasons\Index as BrowseMangaSeasons;
use App\Http\Livewire\Browse\Manga\Upcoming\Index as BrowseMangaUpcomingIndex;
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

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', BrowseMangaUpcomingIndex::class)
                    ->name('.index');
            });

        Route::prefix('/continuing')
            ->name('.continuing')
            ->group(function () {
                Route::get('/', BrowseMangaContinuingIndex::class)
                    ->name('.index');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('manga.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', BrowseMangaSeasonsArchive::class)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('manga.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', BrowseMangaSeasons::class)
                            ->name('.season');
                    });
            });

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
