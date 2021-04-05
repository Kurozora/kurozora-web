<?php

use App\Http\Controllers\WebControllers\ThemeController;
use App\Http\Livewire\Theme\Index as ThemeIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('/themes')
    ->name('themes')
    ->group(function () {
        Route::get('/', [ThemeController::class, 'index'])
        ->name('index');

        Route::get('/create', [ThemeController::class, 'create'])
        ->name('.create');
    });
