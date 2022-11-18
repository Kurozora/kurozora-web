<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{kitsu_url}')
    ->where(['kitsu_url' => '^(www\.)?kitsu(.io)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('kitsu')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:slug}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');

                        Route::get('/{any}', AnimeDetails::class)
                            ->name('.any');
                    });
            });
    });
