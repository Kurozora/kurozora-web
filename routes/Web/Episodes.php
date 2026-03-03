<?php

use App\Livewire\Episode\Details as EpisodeDetails;
use App\Livewire\Episode\Reviews as EpisodeReviews;

Route::prefix('/episodes')
    ->name('episodes')
    ->group(function () {
        // Bind 'episode' manually
        Route::bind('episode', function ($value) {
            if (ctype_digit($value)) {
                $episode = \App\Models\Episode::findOrFail($value);

                $segments = request()->segments();

                foreach ($segments as $i => $segment) {
                    if ($segment === $value) {
                        $segments[$i] = $episode->public_id;
                        break;
                    }
                }

                $newUrl = '/' . implode('/', $segments);

                abort(redirect($newUrl, 301));
            }

            return \App\Models\Episode::where('public_id', $value)->firstOrFail();
        });

        Route::prefix('{episode}')->group(function () {
            Route::get('/', EpisodeDetails::class)
                ->name('.details');

            Route::get('/reviews', EpisodeReviews::class)
                ->name('.reviews');

            Route::get('/edit', function (\App\Models\Episode $episode) {
                return redirect(Nova::path() . '/resources/' . \App\Nova\Episode::uriKey() . '/' . $episode->id);
            })
                ->middleware('auth')
                ->name('.edit');
        });
    });
