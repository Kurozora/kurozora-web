<?php

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\MuseumController;

Route::prefix('/museum')
    ->name('museum')
    ->group(function () {
        Route::get('/anime', [MuseumController::class, 'index'])
            ->defaults('kind', UserLibraryKind::Anime)
            ->name('.anime');

        Route::get('/anime/{year}', [MuseumController::class, 'byYear'])
            ->defaults('kind', UserLibraryKind::Anime)
            ->whereNumber('year')
            ->name('.anime.year');

        Route::get('/manga', [MuseumController::class, 'index'])
            ->defaults('kind', UserLibraryKind::Manga)
            ->name('.manga');

        Route::get('/manga/{year}', [MuseumController::class, 'byYear'])
            ->defaults('kind', UserLibraryKind::Manga)
            ->whereNumber('year')
            ->name('.manga.year');

        Route::get('/games', [MuseumController::class, 'index'])
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.games');

        Route::get('/games/{year}', [MuseumController::class, 'byYear'])
            ->defaults('kind', UserLibraryKind::Game)
            ->whereNumber('year')
            ->name('.games.year');
    });
