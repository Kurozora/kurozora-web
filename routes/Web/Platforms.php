<?php

use App\Http\Livewire\Platform\Details as PlatformDetails;
use App\Http\Livewire\Platform\Index as PlatformIndex;

//use App\Http\Livewire\Platform\Anime as PlatformAnime;
//use App\Http\Livewire\Platform\Games as PlatformGames;
//use App\Http\Livewire\Platform\Manga as PlatformManga;

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
