<?php

use App\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{trakt_url}')
    ->where(['trakt_url' => '^(www\.)?trakt(.tv)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('trakt')
    ->group(function () {
        Route::prefix('/shows')
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
