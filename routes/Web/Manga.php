<?php

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\BrowseController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\MangaController;
use App\Http\Controllers\Web\ParentalGuideController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\TitleCastController;
use App\Http\Controllers\Web\TitleRelationController;
use App\Http\Controllers\Web\TitleStaffController;
use App\Http\Controllers\Web\TitleStudioController;
use App\Models\Manga;

Route::prefix('/manga')
    ->name('manga')
    ->group(function () {
        Route::get('/', [CatalogController::class, 'index'])
            ->defaults('kind', UserLibraryKind::Manga)
            ->name('.index');

        Route::get('/adapted', [CatalogController::class, 'adapted'])
            ->defaults('kind', UserLibraryKind::Manga)
            ->name('.adapted');

        Route::get('/adapted/random', [CatalogController::class, 'randomAdapted'])
            ->defaults('kind', UserLibraryKind::Manga)
            ->name('.adapted.random');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', [BrowseController::class, 'upcoming'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.index');

                Route::get('/random', [BrowseController::class, 'randomUpcoming'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.random');
            });

        Route::prefix('/continuing')
            ->name('.continuing')
            ->group(function () {
                Route::get('/', [BrowseController::class, 'continuing'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.index');

                Route::get('/random', [BrowseController::class, 'randomContinuing'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.random');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('manga.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', [BrowseController::class, 'archive'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('manga.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', [BrowseController::class, 'seasons'])
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->name('.season');

                        Route::get('/{season}/section', [SectionController::class, 'browseSeason'])
                            ->defaults('kind', UserLibraryKind::Manga)
                            ->middleware('auth')
                            ->name('.season.section');
                    });
            });

        Route::prefix('{manga}')
            ->group(function () {
                Route::get('/', [MangaController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'manga'])
                    ->where('section', 'cast|staff|studios|more-by-studio|related-manga|related-anime|related-games|reviews')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/cast', [TitleCastController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.cast');

                Route::get('/edit', function (Manga $manga) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Manga::uriKey() . '/' . $manga->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/parentalguide', [ParentalGuideController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.parentalguide');

                Route::get('/parentalguide/{category}', [ParentalGuideController::class, 'category'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->whereIn('category', ParentalGuideCategory::slugs())
                    ->name('.parentalguide.category');

                Route::get('/related-games', [TitleRelationController::class, 'games'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.related-games');

                Route::get('/related-mangas', [TitleRelationController::class, 'manga'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.related-mangas');

                Route::get('/related-anime', [TitleRelationController::class, 'anime'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.related-anime');

                Route::get('/related-shows', function (Manga $manga) {
                    return redirect()->route('manga.related-anime', $manga, 301);
                })
                    ->name('.related-shows');

                Route::get('/reviews', [ReviewController::class, 'manga'])
                    ->name('.reviews');

                Route::get('/staff', [TitleStaffController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.staff');

                Route::get('/studios', [TitleStudioController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Manga)
                    ->name('.studios');
            });
    });
