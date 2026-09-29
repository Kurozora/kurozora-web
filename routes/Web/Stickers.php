<?php

use App\Http\Controllers\Web\StickerController;

Route::prefix('/stickers')
    ->name('stickers')
    ->middleware('cache.headers:public;max_age=3600;etag')
    ->group(function () {
        Route::get('/', [StickerController::class, 'index'])
            ->name('.index');
    });
