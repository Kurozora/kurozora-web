<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Route;

Route::prefix('/sessions')
    ->name('sessions.')
    ->group(function() {
    Route::post('/', [SessionController::class, 'create']);

    Route::get('/{session}', [SessionController::class, 'details'])
        ->middleware('kurozora.userauth')
        ->middleware('can:get_session,session')
        ->name('details');

    Route::post('/{session}/update', [SessionController::class, 'update'])
        ->middleware('kurozora.userauth')
        ->middleware('can:update_session,session');

    Route::post('/{session}/delete', [SessionController::class, 'delete'])
        ->middleware('kurozora.userauth')
        ->middleware('can:delete_session,session');
});
