<?php

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Livewire\Browse\Seasons\Archive as BrowseSeasonsArchive;
use App\Livewire\Browse\Seasons\Index as BrowseSeasonsIndex;
use App\Livewire\Browse\Upcoming\Index as BrowseUpcomingIndex;
use App\Livewire\Cast;
use App\Livewire\Catalog;
use App\Livewire\Game\Details as GameDetails;
use App\Livewire\ParentalGuide;
use App\Livewire\ParentalGuideCategoryEntries;
use App\Livewire\RelatedGames;
use App\Livewire\RelatedMangas;
use App\Livewire\RelatedShows;
use App\Livewire\Reviews;
use App\Livewire\Songs;
use App\Livewire\Staff;
use App\Livewire\Studios;
use App\Models\Game;

Route::prefix('/games')
    ->name('games')
    ->group(function () {
        Route::get('/', Catalog::class)
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.index');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', BrowseUpcomingIndex::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.index');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('games.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', BrowseSeasonsArchive::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('games.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', BrowseSeasonsIndex::class)
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.season');
                    });
            });

        Route::prefix('{game}')
            ->group(function () {
                Route::get('/', GameDetails::class)
                    ->name('.details');

                Route::get('/cast', Cast::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.cast');

                Route::get('/edit', function (Game $game) {
                    return redirect(Nova::path() . '/resources/' . \App\Nova\Game::uriKey() . '/' . $game->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/parentalguide', ParentalGuide::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.parentalguide');

                Route::get('/parentalguide/{category}', ParentalGuideCategoryEntries::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->whereIn('category', ParentalGuideCategory::slugs())
                    ->name('.parentalguide.category');

                Route::get('/related-games', RelatedGames::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.related-games');

                Route::get('/related-mangas', RelatedMangas::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.related-literatures');

                Route::get('/related-anime', RelatedShows::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.related-anime');

                Route::get('/related-shows', function (Game $game) {
                    return redirect()->route('games.related-anime', $game, 301);
                })
                    ->name('.related-shows');;

                Route::get('/reviews', Reviews::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.reviews');

                Route::get('/songs', Songs::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.songs');

                Route::get('/staff', Staff::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.staff');

                Route::get('/studios', Studios::class)
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.studios');
            });
    });
