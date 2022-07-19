<?php

use App\Http\Livewire\Anime\Cast as AnimeCast;
use App\Http\Livewire\Anime\Details as AnimeDetails;
use App\Http\Livewire\Anime\Index as AnimeIndex;
use App\Http\Livewire\Anime\RelatedShows;
use App\Http\Livewire\Anime\Songs as AnimeSongs;
use App\Http\Livewire\Anime\Studios as AnimeStudios;
use App\Http\Livewire\Browse\Anime\Seasons\Archive as BrowseAnimeSeasonsArchive;
use App\Http\Livewire\Browse\Anime\Seasons\Index as BrowseAnimeSeasons;
use App\Http\Livewire\Browse\Anime\Upcoming\Index as BrowseAnimeUpcomingIndex;
use App\Http\Livewire\Season\Details as SeasonDetails;

Route::prefix('/anime')
    ->name('anime')
    ->group(function () {
        Route::get('/', AnimeIndex::class)
            ->name('.index');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', BrowseAnimeUpcomingIndex::class)
                    ->name('.index');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('anime.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', BrowseAnimeSeasonsArchive::class)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('anime.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', BrowseAnimeSeasons::class)
                            ->name('.season');
                    });
            });

        Route::prefix('{anime}')
            ->group(function () {
                Route::get('/', AnimeDetails::class)
                    ->name('.details');

                Route::get('/cast', AnimeCast::class)
                    ->name('.cast');

                Route::get('/seasons', SeasonDetails::class)
                    ->name('.seasons');

                Route::get('/songs', AnimeSongs::class)
                    ->name('.songs');

                Route::get('/studios', AnimeStudios::class)
                    ->name('.studios');

                Route::get('/related-shows', RelatedShows::class)
                    ->name('.related-shows');
            });
    });
