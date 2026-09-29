<?php

use App\Http\Controllers\Web\AnimeController;
use App\Http\Controllers\Web\CharacterController;
use App\Http\Controllers\Web\MangaController;
use App\Http\Controllers\Web\PersonController;

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
                        Route::get('/', [AnimeController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [AnimeController::class, 'show'])
                            ->name('.any');
                    });
            });

        Route::prefix('/character')
            ->name('.character')
            ->group(function () {
                Route::prefix('{character:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [CharacterController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [CharacterController::class, 'show'])
                            ->name('.any');
                    });
            });

        Route::prefix('/manga')
            ->name('.manga')
            ->group(function () {
                Route::prefix('{manga:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [MangaController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [MangaController::class, 'show'])
                            ->name('.any');
                    });
            });

        Route::prefix('/people')
            ->name('.people')
            ->group(function () {
                Route::prefix('{person:mal_id}')
                    ->name('.details')
                    ->group(function () {
                        Route::get('/', [PersonController::class, 'show'])
                            ->name('.index');

                        Route::get('/{any}', [PersonController::class, 'show'])
                            ->name('.any');
                    });
            });
    });
