<?php

use App\Http\Controllers\Web\PersonController;
use App\Http\Controllers\Web\ReviewController;
use App\Http\Controllers\Web\SectionController;
use App\Models\Person;

Route::prefix('/people')
    ->name('people')
    ->group(function () {
        Route::get('/', [PersonController::class, 'index'])
            ->name('.index');

        Route::get('/random', [PersonController::class, 'random'])
            ->name('.random');

        Route::prefix('{person}')
            ->group(function () {
                Route::get('/', [PersonController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'person'])
                    ->where('section', 'anime|characters|manga|games|reviews')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/anime', [PersonController::class, 'anime'])
                    ->name('.anime');

                Route::get('/characters', [PersonController::class, 'characters'])
                    ->name('.characters');

                Route::get('/edit', function (Person $person) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Person::uriKey() . '/' . $person->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/games', [PersonController::class, 'games'])
                    ->name('.games');

                Route::get('/manga', [PersonController::class, 'manga'])
                    ->name('.manga');

                Route::get('/reviews', [ReviewController::class, 'person'])
                    ->name('.reviews');
            });
    });
