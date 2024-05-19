<?php

use App\Livewire\Platform\Details as PlatformDetails;
use App\Livewire\Platform\Index as PlatformIndex;

Route::prefix('/platforms')
    ->name('platforms')
    ->group(function () {
        Route::get('/', PlatformIndex::class)
            ->name('.index');

        Route::prefix('{platform}')
            ->group(function () {
                Route::get('/', PlatformDetails::class)
                    ->name('.details');
//
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
