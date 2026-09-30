<?php

use App\Enums\ChartKind;
use App\Http\Controllers\Web\ChartController;
use App\Http\Controllers\Web\SectionController;

Route::prefix('/charts')
    ->name('charts')
    ->group(function () {
        Route::get('/', [ChartController::class, 'index'])
            ->name('.index');

        Route::prefix('{chart}')
            ->where(['chart' => implode('|', ChartKind::getValues())])
            ->group(function () {
                Route::get('/', [ChartController::class, 'show'])
                    ->name('.details');

                Route::get('/section', [SectionController::class, 'chart'])
                    ->middleware('auth')
                    ->name('.section');

                Route::get('/top', [ChartController::class, 'show'])
                    ->name('.top');
            });
    });

Route::get('topanime', function () {
    return to_route('charts.top', ['chart' => ChartKind::Anime]);
});

Route::get('topecharacters', function () {
    return to_route('charts.top', ['chart' => ChartKind::Characters]);
});

Route::get('topepisodes', function () {
    return to_route('charts.top', ['chart' => ChartKind::Episodes]);
});

Route::get('topgames', function () {
    return to_route('charts.top', ['chart' => ChartKind::Games]);
});

Route::get('topmanga', function () {
    return to_route('charts.top', ['chart' => ChartKind::Manga]);
});

Route::get('toppeople', function () {
    return to_route('charts.top', ['chart' => ChartKind::People]);
});

Route::get('topsongs', function () {
    return to_route('charts.top', ['chart' => ChartKind::Songs]);
});

Route::get('topstudios', function () {
    return to_route('charts.top', ['chart' => ChartKind::Studios]);
});
