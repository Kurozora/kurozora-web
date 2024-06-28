<?php

use App\Livewire\Anime\Details as AnimeDetails;
use App\Livewire\Character\Details as CharacterDetails;
use App\Livewire\Manga\Details as MangaDetails;
use App\Livewire\Person\Details as PersonDetails;

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

        Route::prefix('/character')
            ->name('.character')
            ->group(function () {
                Route::prefix('{character:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', CharacterDetails::class)
                            ->name('.index');

                        Route::get('/{any}', CharacterDetails::class)
                            ->name('.any');
                    });
            });

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', MangaDetails::class)
                            ->name('.index');

                        Route::get('/{any}', MangaDetails::class)
                            ->name('.any');
                    });
            });

        Route::prefix('/people')
            ->name('.people')
            ->group(function () {
                Route::prefix('{person:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', PersonDetails::class)
                            ->name('.index');

                        Route::get('/{any}', PersonDetails::class)
                            ->name('.any');
                    });
            });
    });
