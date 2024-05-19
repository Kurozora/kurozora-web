<?php

use App\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{notify_url}')
    ->where(['notify_url' => '^(www\.)?notify(.moe)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('notify')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:notify_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');

                        Route::get('/{any}', AnimeDetails::class)
                            ->name('.any');
                    });
            });
    });
