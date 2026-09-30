<?php

use App\Enums\ParentalGuideCategory;
use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\BrowseController;
use App\Http\Controllers\Web\CatalogController;
use App\Http\Controllers\Web\GameController;
use App\Http\Controllers\Web\ParentalGuideController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\TitleCastController;
use App\Http\Controllers\Web\TitleRelationController;
use App\Http\Controllers\Web\TitleSongController;
use App\Http\Controllers\Web\TitleStaffController;
use App\Http\Controllers\Web\TitleStudioController;
use App\Models\Game;

Route::prefix('/games')
    ->name('games')
    ->group(function () {
        Route::get('/', [CatalogController::class, 'index'])
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.index');

        Route::get('/adapted', [CatalogController::class, 'adapted'])
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.adapted');

        Route::get('/adapted/random', [CatalogController::class, 'randomAdapted'])
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.adapted.random');

        Route::get('/trailers', [CatalogController::class, 'trailers'])
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.trailers');

        Route::get('/trailers/random', [CatalogController::class, 'randomTrailer'])
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.trailers.random');

        Route::prefix('/upcoming')
            ->name('.upcoming')
            ->group(function () {
                Route::get('/', [BrowseController::class, 'upcoming'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.index');

                Route::get('/random', [BrowseController::class, 'randomUpcoming'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.random');
            });

        Route::prefix('/seasons')
            ->name('.seasons')
            ->group(function () {
                Route::get('/', function () {
                    return to_route('games.seasons.year.season', [now()->year, season_of_year()->key]);
                })
                    ->name('.index');

                Route::get('/archive', [BrowseController::class, 'archive'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.archive');

                Route::prefix('/{year}')
                    ->name('.year')
                    ->group(function () {
                        Route::get('/', function ($year) {
                            return to_route('games.seasons.year.season', [$year, season_of_year()->key]);
                        })
                            ->name('.index');

                        Route::get('/{season}', [BrowseController::class, 'seasons'])
                            ->defaults('kind', UserLibraryKind::Game)
                            ->name('.season');

                        Route::get('/{season}/section', [SectionController::class, 'browseSeason'])
                            ->defaults('kind', UserLibraryKind::Game)
                            ->middleware('auth')
                            ->name('.season.section');
                    });
            });

        Route::prefix('{game}')
            ->group(function () {
                Route::get('/', [GameController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'game'])
                    ->where('section', 'cast|staff|songs|studios|more-by-studio|related-games|related-anime|related-manga|reviews')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/cast', [TitleCastController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.cast');

                Route::get('/edit', function (Game $game) {
                    return redirect(Nova::path() . '/resources/' . \App\Nova\Game::uriKey() . '/' . $game->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/parentalguide', [ParentalGuideController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.parentalguide');

                Route::get('/parentalguide/{category}', [ParentalGuideController::class, 'category'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->whereIn('category', ParentalGuideCategory::slugs())
                    ->name('.parentalguide.category');

                Route::get('/related-games', [TitleRelationController::class, 'games'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.related-games');

                Route::get('/related-mangas', [TitleRelationController::class, 'manga'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.related-literatures');

                Route::get('/related-anime', [TitleRelationController::class, 'anime'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.related-anime');

                Route::get('/related-shows', function (Game $game) {
                    return redirect()->route('games.related-anime', $game, 301);
                })
                    ->name('.related-shows');

                Route::get('/reviews', [ReviewController::class, 'game'])
                    ->name('.reviews');

                Route::get('/songs', [TitleSongController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.songs');

                Route::get('/staff', [TitleStaffController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.staff');

                Route::get('/studios', [TitleStudioController::class, 'index'])
                    ->defaults('kind', UserLibraryKind::Game)
                    ->name('.studios');
            });
    });
