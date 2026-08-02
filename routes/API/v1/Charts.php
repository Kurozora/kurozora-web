<?php

use App\Enums\ChartKind;
use App\Http\Controllers\API\v1\ChartController;

Route::prefix('/charts')
    ->name('.charts')
    ->middleware('cache.headers:private;no_cache;etag')
    ->group(function () {
        Route::prefix('{chart}')
            ->where(['chart' => implode('|', ChartKind::getValues())])
            ->group(function () {
                Route::get('/', [ChartController::class, 'view'])
                    ->middleware('auth.kurozora:optional')
                    ->name('.view');
            });
    });
