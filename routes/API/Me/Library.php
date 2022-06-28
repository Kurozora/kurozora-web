<?php

use App\Http\Controllers\LibraryController;

Route::prefix('/library')
    ->name('.library')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->middleware('auth.kurozora');

        Route::post('/', [LibraryController::class, 'create'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::post('/delete', [LibraryController::class, 'delete'])
            ->middleware('auth.kurozora')
            ->name('.delete');

        Route::post('/mal-import', [LibraryController::class, 'animeImport'])
            ->middleware('auth.kurozora')
            ->name('.import');

        Route::get('/search', [LibraryController::class, 'search'])
            ->middleware('auth.kurozora')
            ->name('.search');
    });
