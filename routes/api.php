<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;
use App\User;
use Illuminate\Support\Facades\Route;

Route::get('/v1', function() {
    return view('website.api', [
        'openapi_json_file' => asset('openapi.json'),
        'api_logo'          => asset('img/static/logo_xsm.png')
    ]);
});

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

        Route::get('/{animeID}/seasons/{seasonNum}', [AnimeController::class, 'seasonInfo'])
            ->where('animeID', '[0-9]*')
            ->where('seasonNum', '[0-9]*');

        Route::get('/{animeID}/seasons/{seasonNum}/episodes', [AnimeController::class, 'episodesAnime'])
            ->where('animeID', '[0-9]*')
            ->where('seasonNum', '[0-9]*');

        Route::post('/{animeID}/rate', [AnimeController::class, 'rateAnime'])
            ->where('animeID', '[0-9]*')
            ->middleware('kurozora.userauth');

        Route::get('/{id}/episodes', 'AnimeController@episodesAnime')->where('id', '[0-9]*');
    });

    Route::prefix('/forum')->group(function() {
        Route::get('/sections', [ForumController::class, 'getSections']);

        Route::get('/sections/{sectionID}', [ForumController::class, 'sectionDetails'])
            ->where('sectionID', '[0-9]*');

        Route::get('/sections/{sectionID}/threads', [ForumController::class, 'getThreads'])
            ->where('sectionID', '[0-9]*');

        Route::post('/get_thread', 'ForumController@getThread');
        Route::post('/vote_thread', 'ForumController@voteThread')->middleware('kurozora.userauth');
        Route::post('/post_thread', 'ForumController@postThread')->middleware('kurozora.userauth');
        Route::post('/post_reply', 'ForumController@postReply')->middleware('kurozora.userauth');
    });

    Route::prefix('/misc')->group(function() {
        Route::get('/get_privacy_policy', 'MiscController@getPrivacyPolicy');
    });
});