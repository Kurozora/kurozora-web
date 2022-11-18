<?php

use App\Http\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{anisearch_url}')
    ->where(['anisearch_url' => '^(www\.)?anisearch(.com)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('anisearch')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:anisearch_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');

                        Route::get(',{any}', AnimeDetails::class)
                            ->name('.any');
                    });
            });
    });
