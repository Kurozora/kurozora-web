<?php

use App\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{syoboi_url}')
    ->where(['syoboi_url' => '^(cal\.)?syoboi(.jp)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('syoboi')
    ->group(function () {
        Route::prefix('/tid')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:syoboi_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');

                        Route::get('/{any}', AnimeDetails::class)
                            ->name('.any');
                    });
            });
    });
