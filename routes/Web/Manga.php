<?php

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Livewire\Browse\Continuing\Index as BrowseContinuingIndex;
use App\Livewire\Browse\Seasons\Archive as BrowseSeasonsArchive;
use App\Livewire\Browse\Seasons\Index as BrowseSeasonsIndex;
use App\Livewire\Browse\Upcoming\Index as BrowseUpcomingIndex;
use App\Livewire\Cast;
use App\Livewire\Catalog;
use App\Livewire\Manga\Details as MangaDetails;
use App\Livewire\ParentalGuide;
use App\Livewire\ParentalGuideCategoryEntries;
use App\Livewire\RelatedGames;
use App\Livewire\RelatedMangas;
use App\Livewire\RelatedShows;
use App\Livewire\Reviews;
use App\Livewire\Staff;
use App\Livewire\Studios;
use App\Models\Manga;

Route::prefix('/manga')
    ->name('manga')
    ->group(function () {
        Route::get('/', Catalog::class)
            ->defaults('kind', UserLibraryKind::Manga)
            ->name('.index');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', BrowseUpcomingIndex::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.index');
            });

        Route::prefix('/continuing')
            ->name('.continuing')
            ->group(function () {
                Route::get('/', BrowseContinuingIndex::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.index');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('manga.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', BrowseSeasonsArchive::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('manga.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', BrowseSeasonsIndex::class)
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.season');
                    });
            });

        Route::prefix('{manga}')
            ->group(function () {
                Route::get('/', MangaDetails::class)
                    ->name('.details');

                Route::get('/cast', Cast::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.cast');

                Route::get('/edit', function (Manga $manga) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Manga::uriKey() . '/' . $manga->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/parentalguide', ParentalGuide::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.parentalguide');

                Route::get('/parentalguide/{category}', ParentalGuideCategoryEntries::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->whereIn('category', ParentalGuideCategory::slugs())
                    ->name('.parentalguide.category');

                Route::get('/related-games', RelatedGames::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.related-games');

                Route::get('/related-mangas', RelatedMangas::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.related-mangas');

                Route::get('/related-anime', RelatedShows::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.related-anime');

                Route::get('/related-shows', function (Manga $manga) {
                    return redirect()->route('manga.related-anime', $manga, 301);
                })
                    ->name('.related-shows');;

                Route::get('/reviews', Reviews::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.reviews');

                Route::get('/staff', Staff::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.staff');

                Route::get('/studios', Studios::class)
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.studios');
            });
    });
