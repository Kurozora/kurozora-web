<?php

use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Manga\Details as MangaDetails;

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

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:slug}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', MangaDetails::class)
                            ->name('.index');

                        Route::get('/{any}', MangaDetails::class)
                            ->name('.any');
                    });
            });
    });
