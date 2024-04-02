<?php

Route::prefix('/library')
    ->name('library')
    ->middleware(['auth'])
    ->group(function () {
        Route::get('/', function (\Illuminate\Http\Request $request) {
            $parameters = $request->all();
            $parameters['user'] = auth()->user();
            return to_route('profile.anime.library', $parameters);
        })
            ->name('.index');
    });
