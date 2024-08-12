<?php

use App\Http\Controllers\API\v1\MeController;

Route::prefix('/episodes')
    ->middleware('auth.kurozora')
    ->name('.episodes')
    ->group(function () {
        Route::get('/up-next', [MeController::class, 'upcomingEpisodes'])
            ->name('.up-next');
    });
