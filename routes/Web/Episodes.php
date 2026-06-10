<?php

use App\Livewire\Episode\Details as EpisodeDetails;
use App\Livewire\Episode\Reviews as EpisodeReviews;
use App\Models\Episode;

Route::get('/episode/{episode}', function (string $episode) {
    return redirect("/episodes/{$episode}", 301);
})
    ->name('episode.details');

Route::prefix('/episodes')
    ->name('episodes')
    ->group(function () {
        Route::prefix('{episode}')->group(function () {
            Route::get('/', EpisodeDetails::class)
                ->name('.details');

            Route::get('/reviews', EpisodeReviews::class)
                ->name('.reviews');

            Route::get('/edit', function (Episode $episode) {
                return redirect(Nova::path() . '/resources/' . App\Nova\Episode::uriKey() . '/' . $episode->id);
            })
                ->middleware('auth')
                ->name('.edit');
        });
    });
