<?php

use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Manga\Details as MangaDetails;

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

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:anisearch_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', MangaDetails::class)
                            ->name('.index');

                        Route::get(',{any}', MangaDetails::class)
                            ->name('.any');
                    });
            });
    });
