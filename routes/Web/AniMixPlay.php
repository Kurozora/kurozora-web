<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{animix_url}')
    ->where(['animix_url' => '^(www\.)?(animixplay|animix)(.to)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('animixplay')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');

                        Route::get('/{any}', AnimeDetails::class)
                            ->name('.any');
                    });
            });
    });
