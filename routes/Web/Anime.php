<?php

use App\Livewire\Anime\Cast as AnimeCast;
use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Anime\Index as AnimeIndex;
use App\Livewire\Anime\RelatedGames;
use App\Livewire\Anime\RelatedMangas;
use App\Livewire\Anime\RelatedShows;
use App\Livewire\Anime\Reviews as AnimeReviews;
use App\Livewire\Anime\Songs as AnimeSongs;
use App\Livewire\Anime\Staff as AnimeStaff;
use App\Livewire\Anime\Studios as AnimeStudios;
use App\Livewire\Browse\Anime\Continuing\Index as BrowseAnimeContinuingIndex;
use App\Livewire\Browse\Anime\Seasons\Archive as BrowseAnimeSeasonsArchive;
use App\Livewire\Browse\Anime\Seasons\Index as BrowseAnimeSeasons;
use App\Livewire\Browse\Anime\Upcoming\Index as BrowseAnimeUpcomingIndex;
use App\Livewire\Season\Details as SeasonDetails;
use App\Models\Anime;

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

        Route::prefix('/continuing')
            ->name('.continuing')
            ->group(function () {
                Route::get('/', BrowseAnimeContinuingIndex::class)
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

                Route::get('/edit', function (Anime $anime) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Anime::uriKey() . '/' . $anime->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/related-games', RelatedGames::class)
                    ->name('.related-games');

                Route::get('/related-mangas', RelatedMangas::class)
                    ->name('.related-mangas');

                Route::get('/related-shows', RelatedShows::class)
                    ->name('.related-shows');

                Route::get('/reviews', AnimeReviews::class)
                    ->name('.reviews');

                Route::get('/seasons', SeasonDetails::class)
                    ->name('.seasons');

                Route::get('/songs', AnimeSongs::class)
                    ->name('.songs');

                Route::get('/staff', AnimeStaff::class)
                    ->name('.staff');

                Route::get('/studios', AnimeStudios::class)
                    ->name('.studios');
            });
    });
