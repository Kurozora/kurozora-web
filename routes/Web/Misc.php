<?php

use App\Http\Controllers\Web\MiscController;

Route::name('misc')
    ->group(function() {
        Route::get('/team', [MiscController::class, 'team'])
            ->name('.team');

        Route::get('/projects', [MiscController::class, 'projects'])
            ->name('.projects');

        Route::get('/contact', [MiscController::class, 'contact'])
            ->name('.contact');

        Route::get('/press-kit', [MiscController::class, 'pressKit'])
            ->name('.press-kit');
    });
