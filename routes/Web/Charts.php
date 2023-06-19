<?php

use App\Enums\ChartKind;
use App\Http\Livewire\Chart\Details as ChartDetails;
use App\Http\Livewire\Chart\Index as ChartIndex;

Route::prefix('/charts')
    ->name('charts')
    ->group(function () {
        Route::get('/', ChartIndex::class)
            ->name('.index');

        Route::prefix('{chart}')
            ->where(['chart' => implode('|', ChartKind::getValues())])
            ->group(function () {
                Route::get('/', ChartDetails::class)
                    ->name('.details');

                Route::get('/top', ChartDetails::class)
                    ->name('.top');
            });
    });

Route::get('topanime', function () {
    return to_route('charts.top', ['chart' => ChartKind::Anime]);
});
Route::get('topepisodes', function () {
    return to_route('charts.top', ['chart' => ChartKind::Episodes]);
});

Route::get('topmanga', function () {
    return to_route('charts.top', ['chart' => ChartKind::Manga]);
});

Route::get('topgames', function () {
    return to_route('charts.top', ['chart' => ChartKind::Games]);
});

Route::get('topsongs', function () {
    return to_route('charts.top', ['chart' => ChartKind::Songs]);
});
