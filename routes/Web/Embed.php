<?php

use App\Http\Controllers\Web\OEmbedController;
use App\Http\Livewire\Embeds\Episode as EmbedsEpisode;
use App\Http\Livewire\Embeds\Song as EmbedsSong;

Route::prefix('/oembed')
    ->name('oembed')
    ->group(function () {
        Route::get('/', [OEmbedController::class, 'show']);
    });

Route::prefix('/embed')
    ->name('embed')
    ->group(function () {
        Route::prefix('/episodes/{episode}')
            ->group(function () {
                Route::get('/', EmbedsEpisode::class)
                    ->name('.episodes');
            });

        Route::prefix('/songs/{song}')
            ->group(function () {
                Route::get('/', EmbedsSong::class)
                    ->name('.songs');
            });
    });

