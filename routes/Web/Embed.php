<?php

use App\Http\Controllers\Web\OEmbedController;
use App\Livewire\Embeds\Episode as EmbedsEpisode;
use App\Livewire\Embeds\Song as EmbedsSong;

Route::prefix('/oembed')
    ->name('oembed')
    ->middleware(['headers.http-accept:json'])
    ->group(function () {
        Route::get('/', [OEmbedController::class, 'show']);
    });

Route::prefix('/embed')
    ->name('embed')
    ->middleware(['headers.http-csp'])
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

