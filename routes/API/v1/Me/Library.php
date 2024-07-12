<?php

use App\Http\Controllers\API\v1\LibraryController;
use App\Http\Controllers\API\v1\MeController;

Route::prefix('/library')
    ->name('.library')
    ->group(function () {
        Route::get('/', [MeController::class, 'getLibrary'])
            ->name('.index');

        Route::post('/', [LibraryController::class, 'create'])
            ->name('.create');

        Route::post('/update', [LibraryController::class, 'update'])
            ->name('.update');

        Route::post('/delete', [LibraryController::class, 'delete'])
            ->name('.delete');

        Route::post('/import', [LibraryController::class, 'import'])
            ->name('.import');
    });
