<?php

use App\Livewire\Anime\Details as AnimeDetails;

Route::prefix('/{imdb_url}')
    ->where(['imdb_url' => '^(www\.)?imdb(.com)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('imdb')
    ->group(function () {
        Route::prefix('/title')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:imdb_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', AnimeDetails::class)
                            ->name('.index');
                    });
            });
    });
