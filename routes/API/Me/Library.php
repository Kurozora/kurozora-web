<?php

use App\Http\Controllers\LibraryController;

Route::prefix('/library')
    ->name('.library')
    ->group(function() {
        Route::get('/', [LibraryController::class, 'index'])
            ->middleware('auth.kurozora');

        Route::post('/', [LibraryController::class, 'addLibrary'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::post('/delete', [LibraryController::class, 'delLibrary'])
            ->middleware('auth.kurozora')
            ->name('.delete');

        Route::post('/mal-import', [LibraryController::class, 'malImport'])
            ->middleware('auth.kurozora')
            ->name('.mal-import');

        Route::get('/search', [LibraryController::class, 'search'])
            ->middleware('auth.kurozora')
            ->name('.search');
    });
