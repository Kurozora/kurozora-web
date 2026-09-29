<?php

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\AnimeController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\SeasonController;
use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\TitleCastController;
use App\Http\Controllers\Web\TitleRelationController;
use App\Http\Controllers\Web\TitleSongController;
use App\Http\Controllers\Web\TitleStaffController;
use App\Http\Controllers\Web\TitleStudioController;
use App\Livewire\Browse\Continuing\Index as BrowseContinuingIndex;
use App\Livewire\Browse\Seasons\Archive as BrowseSeasonsArchive;
use App\Livewire\Browse\Seasons\Index as BrowseSeasonsIndex;
use App\Livewire\Browse\Upcoming\Index as BrowseUpcomingIndex;
use App\Livewire\ParentalGuide;
use App\Livewire\ParentalGuideCategoryEntries;
use App\Livewire\Reviews;
use App\Livewire\Trailers;
use App\Models\Anime;

Route::prefix('/anime')
    ->name('anime')
    ->group(function () {
        Route::get('/', [CatalogController::class, 'index'])
            ->defaults('kind', UserLibraryKind::Anime)
            ->name('.index');

        Route::get('/trailers', Trailers::class)
            ->defaults('kind', UserLibraryKind::Anime)
            ->name('.trailers');

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
                Route::get('/', [AnimeController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'anime'])
                    ->where('section', 'seasons|cast|staff|songs|studios|more-by-studio|related-anime|related-manga|related-games')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/cast', [TitleCastController::class, 'index'])
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

                Route::get('/related-games', [TitleRelationController::class, 'games'])
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.related-games');

                Route::get('/related-mangas', [TitleRelationController::class, 'manga'])
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.related-mangas');

                Route::get('/related-anime', [TitleRelationController::class, 'anime'])
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.related-anime');

                Route::get('/related-shows', function (Anime $anime) {
                    return redirect()->route('anime.related-anime', $anime, 301);
                })
                    ->name('.related-shows');;

                Route::get('/reviews', Reviews::class)
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.reviews');

                Route::get('/seasons', [SeasonController::class, 'index'])
                    ->name('.seasons');

                Route::get('/songs', [TitleSongController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.songs');

                Route::get('/staff', [TitleStaffController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.staff');

                Route::get('/studios', [TitleStudioController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Anime)
                    ->name('.studios');
            });
    });
