<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{mal_url}')
    ->where(['mal_url' => '^(www\.)?(myanimelist|mal)(.net)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('myanimelist')
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
