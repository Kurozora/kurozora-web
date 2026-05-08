<?php

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Browse\Continuing\Index as BrowseContinuingIndex;
use App\Livewire\Browse\Seasons\Archive as BrowseSeasonsArchive;
use App\Livewire\Browse\Seasons\Index as BrowseSeasonsIndex;
use App\Livewire\Browse\Upcoming\Index as BrowseUpcomingIndex;
use App\Livewire\Cast;
use App\Livewire\Catalog;
use App\Livewire\ParentalGuide;
use App\Livewire\ParentalGuideCategoryEntries;
use App\Livewire\RelatedGames;
use App\Livewire\RelatedMangas;
use App\Livewire\RelatedShows;
use App\Livewire\Reviews;
use App\Livewire\Season\Details as SeasonDetails;
use App\Livewire\Songs;
use App\Livewire\Staff;
use App\Livewire\Studios;
use App\Models\Anime;

Route::prefix('/anime')
    ->name('anime')
    ->group(function () {
        Route::get('/', Catalog::class)
            ->defaults('kind', UserLibraryKind::Anime)
            ->name('.index');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', BrowseUpcomingIndex::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.index');
            });

        Route::prefix('/continuing')
            ->name('.continuing')
            ->group(function () {
                Route::get('/', BrowseContinuingIndex::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.index');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('anime.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', BrowseSeasonsArchive::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('anime.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', BrowseSeasonsIndex::class)
                            ->defaults('kind', UserLibraryKind::Anime)
                            ->name('.season');
                    });
            });

        Route::prefix('{anime}')
            ->group(function () {
                Route::get('/', AnimeDetails::class)
                    ->name('.details');

                Route::get('/cast', Cast::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.cast');

                Route::get('/edit', function (Anime $anime) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Anime::uriKey() . '/' . $anime->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/parentalguide', ParentalGuide::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.parentalguide');

                Route::get('/parentalguide/{category}', ParentalGuideCategoryEntries::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->whereIn('category', ParentalGuideCategory::slugs())
                    ->name('.parentalguide.category');

                Route::get('/related-games', RelatedGames::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.related-games');

                Route::get('/related-mangas', RelatedMangas::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.related-mangas');

                Route::get('/related-anime', RelatedShows::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.related-anime');

                Route::get('/related-shows', function (Anime $anime) {
                    return redirect()->route('anime.related-anime', $anime, 301);
                })
                    ->name('.related-shows');;

                Route::get('/reviews', Reviews::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.reviews');

                Route::get('/seasons', SeasonDetails::class)
                    ->name('.seasons');

                Route::get('/songs', Songs::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.songs');

                Route::get('/staff', Staff::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.staff');

                Route::get('/studios', Studios::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.studios');
            });
    });
