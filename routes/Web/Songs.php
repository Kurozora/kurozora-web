<?php

use App\Http\Controllers\Web\SongLyricsController;
use App\Livewire\Song\Details as SongDetails;
use App\Livewire\Song\Index as SongIndex;
use App\Livewire\Song\Reviews as SongReviews;
use App\Models\Song;

Route::prefix('/songs')
    ->name('songs')
    ->group(function () {
        Route::get('/', SongIndex::class)
            ->name('.index');

        Route::prefix('{song}')
            ->group(function () {
                Route::get('/', SongDetails::class)
                    ->name('.details');

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
