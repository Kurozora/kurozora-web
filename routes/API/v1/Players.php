<?php

use App\Http\Controllers\API\v1\PlayerController;

Route::prefix('/players')
    ->name('.players')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [PlayerController::class, 'index'])
            ->name('.index');
    });
