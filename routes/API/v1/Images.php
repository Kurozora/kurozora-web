<?php

use App\Http\Controllers\API\v1\ImageController;

Route::prefix('/images')
    ->name('.images')
    ->group(function () {
        Route::get('/random', [ImageController::class, 'random'])
            ->middleware('auth.kurozora:optional')
            ->name('.random');
    });
