<?php

use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Manga\Details as MangaDetails;

Route::prefix('/{anime_planet_url}')
    ->where(['anime_planet_url' => '^(www\.)?anime(-)?planet.com'])
    ->middleware(['auth', 'user.is-pro-or-subscribed'])
    ->name('animeplanet')
    ->group(function () {
        Route::prefix('/anime')
            ->name('.anime')
            ->group(function () {
                Route::prefix('{anime:animeplanet_id}')
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
                Route::prefix('{manga:animeplanet_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', MangaDetails::class)
                            ->name('.index');

                        Route::get('/{any}', MangaDetails::class)
                            ->name('.any');
                    });
            });
    });
