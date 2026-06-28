<?php

use App\Http\Controllers\API\v1\DigestController;

Route::prefix('/digest')
    ->middleware(['auth.kurozora', 'cache.headers:private;no_cache;etag'])
    ->name('.digest')
    ->group(function () {
        Route::get('/', [DigestController::class, 'index'])
            ->name('.index');

        Route::get('/{section}', [DigestController::class, 'section'])
            ->name('.section');
    });
