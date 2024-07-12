<?php

use App\Http\Controllers\API\v1\APIController;
use App\Livewire\Misc\ApiIndex;

Route::get('/', [APIController::class, 'index'])
    ->name('api.index');

Route::prefix('/v1')
    ->name('api')
    ->group(function () {
        Route::get('/', ApiIndex::class);

        Route::get('/info', [APIController::class, 'info'])
            ->name('.info');

        Route::get('/settings', [APIController::class, 'settings'])
            ->name('.settings');

        require 'API/v1/Anime.php';
        require 'API/v1/Cast.php';
        require 'API/v1/Characters.php';
        require 'API/v1/Episodes.php';
        require 'API/v1/Explore.php';
        require 'API/v1/Games.php';
        require 'API/v1/Genres.php';
        require 'API/v1/Images.php';
        require 'API/v1/Feed.php';
        require 'API/v1/Languages.php';
        require 'API/v1/Legal.php';
        require 'API/v1/Manga.php';
        require 'API/v1/Me.php';
        require 'API/v1/MyAnimeList.php';
        require 'API/v1/People.php';
        require 'API/v1/Reviews.php';
        require 'API/v1/Schedule.php';
        require 'API/v1/Search.php';
        require 'API/v1/Seasons.php';
        require 'API/v1/Songs.php';
        require 'API/v1/Studios.php';
        require 'API/v1/Store.php';
        require 'API/v1/Themes.php';
        require 'API/v1/Theme Store.php';
        require 'API/v1/Users.php';
    });

Route::get('/{wordpress_url}', [APIController::class, 'markSpammer'])
    ->where(['wordpress_url' => '(?:[a-zA-Z0-9_-]+\/)?(wordpress|wp-includes|wp-admin|wp-content).*'])
    ->name('.wordpress');

Route::fallback([APIController::class, 'error'])
    ->name('.fallback');
