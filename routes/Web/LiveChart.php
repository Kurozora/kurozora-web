<?php

use App\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{livechart_url}')
    ->where(['livechart_url' => '^(www\.)?livechart(.me)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('livechart')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:livechart_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');

                        Route::get('/{any}', AnimeDetails::class)
                            ->name('.any');
                    });
            });
    });
