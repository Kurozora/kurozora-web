<?php

namespace App\Http\Controllers;

use App\Http\Livewire\Misc\ApiIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', [APIController::class, 'index'])
    ->name('api.index');

Route::prefix('/v1')
    ->name('api')
    ->group(function () {
        Route::get('/', ApiIndex::class);

        Route::get('/info', [APIController::class, 'info'])
            ->name('.info');

        Route::get('/explore', [ExplorePageController::class, 'explore'])
            ->middleware('auth.kurozora:optional')
            ->name('.explore');

        require 'API/Anime.php';
        require 'API/Cast.php';
        require 'API/Characters.php';
        require 'API/Episodes.php';
        require 'API/Genres.php';
        require 'API/Feed.php';
        require 'API/Languages.php';
        require 'API/Legal.php';
        require 'API/Me.php';
        require 'API/People.php';
        require 'API/Seasons.php';
        require 'API/Studios.php';
        require 'API/Store.php';
        require 'API/Themes.php';
        require 'API/Theme Store.php';
        require 'API/Users.php';
    });

Route::fallback([APIController::class, 'error'])
    ->name('.fallback');
