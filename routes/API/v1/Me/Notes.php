<?php

use App\Http\Controllers\API\v1\UserNoteController;

Route::prefix('/notes')
    ->middleware('auth.kurozora')
    ->name('.notes')
    ->group(function () {
        Route::post('/', [UserNoteController::class, 'set'])
            ->name('.set');
    });
