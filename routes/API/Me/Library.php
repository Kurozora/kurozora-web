<?php

use App\Http\Controllers\LibraryController;

Route::prefix('/library')
    ->name('.library')
    ->group(function() {
        Route::get('/', [LibraryController::class, 'index'])
            ->middleware('kurozora.userauth');

        Route::post('/', [LibraryController::class, 'addLibrary'])
            ->middleware('kurozora.userauth')
            ->name('.create');

        Route::post('/delete', [LibraryController::class, 'delLibrary'])
            ->middleware('kurozora.userauth')
            ->name('.delete');

        Route::post('/mal-import', [LibraryController::class, 'malImport'])
            ->middleware('kurozora.userauth')
            ->name('.mal-import');

        Route::get('/search', [LibraryController::class, 'search'])
            ->middleware('kurozora.userauth')
            ->name('.search');
    });
