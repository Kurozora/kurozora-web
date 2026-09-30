<?php

use App\Http\Controllers\Web\CharacterController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\SectionController;
use App\Models\Character;

Route::prefix('/characters')
    ->name('characters')
    ->group(function () {
        Route::get('/', [CharacterController::class, 'index'])
            ->name('.index');

        Route::get('/random', [CharacterController::class, 'random'])
            ->name('.random');

        Route::prefix('{character}')
            ->group(function () {
                Route::get('/', [CharacterController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'character'])
                    ->where('section', 'anime|people|manga|games|reviews')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/anime', [CharacterController::class, 'anime'])
                    ->name('.anime');

                Route::get('/edit', function (Character $character) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Character::uriKey() . '/' . $character->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/games', [CharacterController::class, 'games'])
                    ->name('.games');

                Route::get('/manga', [CharacterController::class, 'manga'])
                    ->name('.manga');

                Route::get('/people', [CharacterController::class, 'people'])
                    ->name('.people');

                Route::get('/reviews', [ReviewController::class, 'character'])
                    ->name('.reviews');
            });
    });
