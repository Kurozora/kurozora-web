<?php

use App\Http\Controllers\Web\PlatformController;
use App\Http\Controllers\Web\SectionController;
use App\Livewire\Platform\Index as PlatformIndex;
use App\Models\Platform;

Route::prefix('/platforms')
    ->name('platforms')
    ->group(function () {
        Route::get('/', PlatformIndex::class)
            ->name('.index');

        Route::prefix('{platform}')
            ->group(function () {
                Route::get('/', [PlatformController::class, 'show'])
                    ->name('.details');

                Route::get('/sections/{section}', [SectionController::class, 'platform'])
                    ->where('section', 'games')
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/edit', function (Platform $platform) {
                    return redirect(Nova::path() . '/resources/'. \App\Nova\Platform::uriKey() . '/' . $platform->id);
                })
                    ->middleware('auth')
                    ->name('.edit');

//                Route::get('/anime', PlatformAnime::class)
//                    ->name('.anime');
//
//                Route::get('/manga', PlatformManga::class)
//                    ->name('.manga');
//
//                Route::get('/games', PlatformGames::class)
//                    ->name('.games');
            });
    });
