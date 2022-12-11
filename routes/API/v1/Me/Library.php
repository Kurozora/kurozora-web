<?php

use App\Http\Controllers\API\v1\LibraryController;

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
    });
