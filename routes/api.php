<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Route;

// API Swagger documentation page
Route::get('/v1', function() {
    return view('website.api', [
        'openapi_json_file' => asset('openapi.json'),
        'api_logo'          => asset('img/static/logo_xsm.png')
    ]);
});

// API Routes
Route::group([/*'middleware' => ['kurozora.useragent'],*/ 'prefix' => 'v1'], function () {
    Route::prefix('/users')->group(function() {
        Route::post('/', [UserController::class, 'register']);

        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/{userID}/sessions', [UserController::class, 'getSessions'])
            ->where('userID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::get('/{userID}/library', [LibraryController::class, 'getLibrary'])
            ->where('userID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::post('/{userID}/library', [LibraryController::class, 'addLibrary'])
            ->where('userID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::post('/{userID}/library/delete', [LibraryController::class, 'delLibrary'])
            ->where('userID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::post('/{userID}/authenticate-channel', [UserController::class, 'authenticateChannel'])
            ->where('userID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::get('/{userID}/profile', [UserController::class, 'profile'])
            ->where('userID', '[0-9]*');

        Route::get('/{userID}/notifications', [UserController::class, 'getNotifications'])
            ->where('userID', '[0-9]*')
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/sessions')->group(function() {
        Route::post('/', [SessionController::class, 'create']);

        Route::get('/{sessionID}', [SessionController::class, 'details'])
            ->where('sessionID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::post('/{sessionID}/validate', [SessionController::class, 'validateSession'])
            ->where('sessionID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::post('/{sessionID}/delete', [SessionController::class, 'delete'])
            ->where('sessionID', '[0-9]*')
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/anime')->group(function() {
        Route::get('/', [AnimeController::class, 'exploreAnime']);

        Route::get('/{animeID}', [AnimeController::class, 'detailsAnime'])
            ->where('animeID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::get('/{animeID}/actors', [AnimeController::class, 'actorsAnime'])
            ->where('animeID', '[0-9]*');

        Route::get('/{animeID}/seasons', [AnimeController::class, 'seasonsAnime'])
            ->where('animeID', '[0-9]*');

        Route::post('/{animeID}/rate', [AnimeController::class, 'rateAnime'])
            ->where('animeID', '[0-9]*')
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/anime-seasons')->group(function() {
        Route::get('/{seasonID}', [AnimeSeasonController::class, 'details'])
            ->where('seasonID', '[0-9]*');

        Route::get('/{seasonID}/episodes', [AnimeSeasonController::class, 'episodes'])
            ->where('seasonID', '[0-9]*');
    });

    Route::prefix('/anime-episodes')->group(function() {
        Route::post('/{episodeID}/watched', [AnimeEpisodeController::class, 'watched'])
            ->where('episodeID', '[0-9]*')
            ->middleware('kurozora.userauth');

    });

    Route::prefix('/forum-sections')->group(function() {
        Route::get('/', [ForumSectionController::class, 'overview']);

        Route::get('/{sectionID}', [ForumSectionController::class, 'details'])
            ->where('sectionID', '[0-9]*');

        Route::get('/{sectionID}/threads', [ForumSectionController::class, 'threads'])
            ->where('sectionID', '[0-9]*');

        Route::post('/{sectionID}/threads', [ForumSectionController::class, 'postThread'])
            ->where('sectionID', '[0-9]*')
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/forum-threads')->group(function() {
        Route::get('/{threadID}', [ForumThreadController::class, 'threadInfo'])
            ->where('threadID', '[0-9]*');

        Route::post('/{threadID}/vote', [ForumThreadController::class, 'vote'])
            ->where('threadID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::get('/{threadID}/replies', [ForumThreadController::class, 'replies'])
            ->where('threadID', '[0-9]*');

        Route::post('/{threadID}/replies', [ForumThreadController::class, 'postReply'])
            ->where('threadID', '[0-9]*')
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/forum-replies')->group(function() {
        Route::post('/{replyID}/vote', [ForumReplyController::class, 'vote'])
            ->where('replyID', '[0-9]*')
            ->middleware('kurozora.userauth');
    });

    Route::get('/privacy-policy', [MiscController::class, 'getPrivacyPolicy']);
});