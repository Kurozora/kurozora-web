<?php

use App\Http\Controllers\API\v1\LibraryController;

Route::prefix('/library')
    ->name('.library')
    ->group(function () {
        Route::get('/', [LibraryController::class, 'index'])
            ->middleware('auth.kurozora')
            ->name('.index');

        Route::post('/', [LibraryController::class, 'create'])
            ->middleware('auth.kurozora')
            ->name('.create');

        Route::post('/update', [LibraryController::class, 'update'])
            ->middleware('auth.kurozora')
            ->name('.update');

        Route::post('/delete', [LibraryController::class, 'delete'])
            ->middleware('auth.kurozora')
            ->name('.delete');

        Route::post('/import', [LibraryController::class, 'import'])
            ->middleware('auth.kurozora')
            ->name('.import');
    });
