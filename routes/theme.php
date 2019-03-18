<?php

use App\Http\Controllers\WebControllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/themes', [ThemeController::class, 'index'])
    ->name('themes.index');

Route::get('/themes/create', [ThemeController::class, 'create'])
    ->name('themes.create');