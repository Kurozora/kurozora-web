<?php

use App\Http\Controllers\API\v1\StickerController;

Route::prefix('/stickers')
    ->name('.stickers')
    ->middleware('cache.headers:public;max_age=86400;etag')
    ->group(function () {
        Route::get('whatsapp/kurochan/bundle', [StickerController::class, 'whatsAppBundle'])
            ->name('.whatsapp.kurochan.bundle');
    });
