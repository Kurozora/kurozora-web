<?php

use App\Http\Controllers\Web\SectionController;
use App\Http\Controllers\Web\SongController;
use App\Http\Controllers\Web\SongLyricsController;
use App\Livewire\Song\Reviews as SongReviews;
use App\Models\Song;

Route::prefix('/songs')
    ->name('songs')
    ->group(function () {
        Route::get('/', [SongController::class, 'index'])
            ->name('.index');

        Route::get('/random', [SongController::class, 'random'])
            ->name('.random');

        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', [SongController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'song'])
                    ->where('section', 'anime|games')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/lyrics', [SongLyricsController::class, 'show'])
                    ->name('.lyrics');

                Route::get('/edit', function (Song $song) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Song::uriKey() . '/' . $song->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/reviews', SongReviews::class)
                    ->name('.reviews');
            });
    });
