<?php

use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Manga\Details as MangaDetails;

Route::prefix('/{anilist_url}')
    ->where(['anilist_url' => '^(www\.)?anilist(.co)?'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('anilist')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:anilist_id}')
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
                Route::prefix('{manga:anilist_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', MangaDetails::class)
                            ->name('.index');

                        Route::get('/{any}', MangaDetails::class)
                            ->name('.any');
                    });
            });
    });
