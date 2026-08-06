<?php

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Web\MuseumController;
use App\Livewire\Museum\Index;

Route::prefix('/museum')
    ->name('museum')
    ->group(function () {
        Route::get('/anime', Index::class)
            ->defaults('kind', UserLibraryKind::Anime)
            ->name('.anime');

        Route::get('/anime/{year}', [MuseumController::class, 'byYear'])
            ->defaults('kind', UserLibraryKind::Anime)
            ->whereNumber('year')
            ->name('.anime.year');

        Route::get('/manga', Index::class)
            ->defaults('kind', UserLibraryKind::Manga)
            ->name('.manga');

        Route::get('/manga/{year}', [MuseumController::class, 'byYear'])
            ->defaults('kind', UserLibraryKind::Manga)
            ->whereNumber('year')
            ->name('.manga.year');

        Route::get('/games', Index::class)
            ->defaults('kind', UserLibraryKind::Game)
            ->name('.games');

        Route::get('/games/{year}', [MuseumController::class, 'byYear'])
            ->defaults('kind', UserLibraryKind::Game)
            ->whereNumber('year')
            ->name('.games.year');
    });
