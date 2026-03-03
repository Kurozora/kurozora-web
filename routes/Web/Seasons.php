<?php

use App\Livewire\Season\Episodes;
use App\Models\Season;

Route::prefix('/seasons')
    ->name('seasons')
    ->group(function () {
        // Bind 'season' manually
        Route::bind('season', function ($value) {
            if (ctype_digit($value)) {
                $season = \App\Models\Season::findOrFail($value);

                $segments = request()->segments();

                foreach ($segments as $i => $segment) {
                    if ($segment === $value) {
                        $segments[$i] = $season->public_id;
                        break;
                    }
                }

                $newUrl = '/' . implode('/', $segments);

                abort(redirect($newUrl, 301));
            }

            return \App\Models\Season::where('public_id', $value)->firstOrFail();
        });

        Route::prefix('{season}')
            ->group(function () {
                Route::get('/edit', function (Season $season) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Season::uriKey() . '/' . $season->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

                Route::get('/episodes', Episodes::class)
                    ->name('.episodes');
            });
    });
