<?php

use App\Http\Livewire\Browse\Game\Seasons\Archive as BrowseGameSeasonsArchive;
use App\Http\Livewire\Browse\Game\Seasons\Index as BrowseGameSeasons;
use App\Http\Livewire\Browse\Game\Upcoming\Index as BrowseGameUpcomingIndex;
use App\Http\Livewire\Game\Cast as GameCast;
use App\Http\Livewire\Game\Details as GameDetails;
use App\Http\Livewire\Game\Index as GameIndex;
use App\Http\Livewire\Game\RelatedGames;
use App\Http\Livewire\Game\RelatedLiteratures;
use App\Http\Livewire\Game\RelatedShows;
use App\Http\Livewire\Game\Songs as GameSongs;
use App\Http\Livewire\Game\Staff as GameStaff;
use App\Http\Livewire\Game\Studios as GameStudios;

Route::prefix('/games')
    ->name('games')
    ->group(function () {
        Route::get('/', GameIndex::class)
            ->name('.index');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', BrowseGameUpcomingIndex::class)
                    ->name('.index');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('games.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', BrowseGameSeasonsArchive::class)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('games.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', BrowseGameSeasons::class)
                            ->name('.season');
                    });
            });

        Route::prefix('{game}')
            ->group(function () {
                Route::get('/', GameDetails::class)
                    ->name('.details');

                Route::get('/cast', GameCast::class)
                    ->name('.cast');

                Route::get('/staff', GameStaff::class)
                    ->name('.staff');

                Route::get('/songs', GameSongs::class)
                    ->name('.songs');

                Route::get('/studios', GameStudios::class)
                    ->name('.studios');

                Route::get('/related-games', RelatedGames::class)
                    ->name('.related-games');

                Route::get('/related-shows', RelatedShows::class)
                    ->name('.related-shows');

                Route::get('/related-mangas', RelatedLiteratures::class)
                    ->name('.related-literatures');
            });
    });