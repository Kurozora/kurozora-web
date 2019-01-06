<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;
use App\UserNotification;
use Illuminate\Support\Facades\Route;

// API Swagger documentation page
Route::get('/v1', function() {
    return view('website.api', [
        'openapi_json_file' => asset('openapi.json'),
        'api_logo'          => asset('img/static/logo_xsm.png')
    ]);
});

// API Routes
Route::group(['prefix' => 'v1'], function () {
    Route::prefix('/users')->group(function() {
        Route::post('/', [UserController::class, 'register']);

        Route::get('/search', [UserController::class, 'search']);

        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/{user}/sessions', [UserController::class, 'getSessions'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_sessions,user');

        Route::get('/{user}/library', [LibraryController::class, 'getLibrary'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_library,user');

        Route::post('/{user}/library', [LibraryController::class, 'addLibrary'])
            ->middleware('kurozora.userauth')
            ->middleware('can:add_to_library,user');

        Route::post('/{user}/library/delete', [LibraryController::class, 'delLibrary'])
            ->middleware('kurozora.userauth')
            ->middleware('can:del_from_library,user');

        Route::post('/{user}/authenticate-channel', [UserController::class, 'authenticateChannel'])
            ->middleware('kurozora.userauth')
            ->middleware('can:authenticate_pusher_channel,user');

        Route::get('/{user}/profile', [UserController::class, 'profile']);

        Route::post('/{user}/profile', [UserController::class, 'updateProfile'])
            ->middleware('kurozora.userauth')
            ->middleware('can:update_profile,user');

        Route::get('/{user}/notifications', [UserController::class, 'getNotifications'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_notifications,user');
    });

    Route::prefix('/user-notifications')->group(function() {
        Route::get('/{notification}', [UserNotificationController::class, 'getNotification'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_notification,notification');

        Route::post('/{notification}/delete', [UserNotificationController::class, 'delete'])
            ->middleware('kurozora.userauth')
            ->middleware('can:del_notification,notification');
    });

    Route::prefix('/sessions')->group(function() {
        Route::post('/', [SessionController::class, 'create']);

        Route::get('/{session}', [SessionController::class, 'details'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_session,session');

        Route::post('/{session}/validate', [SessionController::class, 'validateSession'])
            ->middleware('kurozora.userauth')
            ->middleware('can:validate_session,session');

        Route::post('/{session}/delete', [SessionController::class, 'delete'])
            ->middleware('kurozora.userauth')
            ->middleware('can:delete_session,session');
    });

    Route::prefix('/anime')->group(function() {
        Route::get('/', [AnimeController::class, 'exploreAnime']);

        Route::get('/search', [AnimeController::class, 'search']);

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
            ->where('seasonID', '[0-9]*')
            ->middleware('kurozora.userauth');
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
        Route::get('/search', [ForumThreadController::class, 'search']);

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