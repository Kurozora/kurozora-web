<?php

use App\Http\Controllers\API\v1\ProviderController;

Route::prefix('/providers')
    ->name('.providers')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::get('/', [ProviderController::class, 'index'])
            ->name('.index');
    });
