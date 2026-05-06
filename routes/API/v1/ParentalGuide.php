<?php

use App\Http\Controllers\API\v1\ParentalGuideEntryController;

Route::prefix('/parentalguide')
    ->name('.parentalguide')
    ->group(function () {
        Route::prefix('/entries/{parentalGuideEntry}')
            ->middleware('auth.kurozora')
            ->group(function () {
                Route::delete('/delete', [ParentalGuideEntryController::class, 'destroy'])
                    ->name('.delete');

                Route::post('/update', [ParentalGuideEntryController::class, 'update'])
                    ->name('.update');

                Route::post('/vote', [ParentalGuideEntryController::class, 'vote'])
                    ->name('.vote');

                Route::post('/report', [ParentalGuideEntryController::class, 'report'])
                    ->name('.report');
            });
    });
