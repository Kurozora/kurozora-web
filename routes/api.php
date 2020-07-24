<?php

namespace App\Http\Controllers;

use App\Http\Controllers\WebControllers\APIDocumentationController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'v1'], function () {
    Route::get('/', [APIDocumentationController::class, 'render']);

    Route::get('/info', [APIController::class, 'info']);

    Route::get('/explore', [ExplorePageController::class, 'explore'])
        ->middleware('kurozora.userauth:optional');

    Route::get('/privacy-policy', [MiscController::class, 'getPrivacyPolicy']);

    require 'API/Users.php';
    require 'API/Notifications.php';
    require 'API/Sessions.php';
    require 'API/Actors.php';
    require 'API/Anime.php';
    require 'API/Anime-Seasons.php';
    require 'API/Anime-Episodes.php';
    require 'API/Characters.php';
    require 'API/Genres.php';
    require 'API/Forum-Sections.php';
    require 'API/Forum-Threads.php';
    require 'API/Forum-Replies.php';
    require 'API/Studios.php';
    require 'API/Themes.php';
});
