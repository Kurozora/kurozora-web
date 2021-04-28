<?php

namespace App\Http\Controllers;

use App\Http\Livewire\Misc\ApiIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')
    ->name('api')
    ->group(function () {
        Route::get('/', ApiIndex::class);

        Route::get('/info', [APIController::class, 'info'])
            ->name('.info');

        Route::get('/explore', [ExplorePageController::class, 'explore'])
            ->middleware('kurozora.userauth:optional')
            ->name('.explore');

        require 'API/Actors.php';
        require 'API/Anime.php';
        require 'API/Anime-Episodes.php';
        require 'API/Anime-Seasons.php';
        require 'API/Characters.php';
        require 'API/Genres.php';
        require 'API/Feed.php';
        require 'API/Forum-Replies.php';
        require 'API/Forum-Sections.php';
        require 'API/Forum-Threads.php';
        require 'API/Legal.php';
        require 'API/Me.php';
        require 'API/Studios.php';
        require 'API/Store.php';
        require 'API/Themes.php';
        require 'API/Users.php';

        Route::fallback([APIController::class, 'error'])
            ->name('.fallback');
    });
