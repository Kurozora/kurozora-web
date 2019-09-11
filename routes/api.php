<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

namespace App\Http\Controllers;
use App\Genre;
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
    Route::get('/info', [APIController::class, 'info']);

    Route::get('/explore', [ExplorePageController::class, 'explore'])
        ->middleware('kurozora.userauth:optional');

    Route::prefix('/users')->group(function() {
        Route::post('/', [UserController::class, 'register']);

        Route::get('/search', [UserController::class, 'search']);

        Route::post('/reset-password', [UserController::class, 'resetPassword']);

        Route::get('/{user}/sessions', [UserController::class, 'getSessions'])
            ->middleware('kurozora.userauth')
            ->middleware('can:get_sessions,user');

        Route::post('/{user}/follow', [FollowingController::class, 'followUser'])
            ->middleware('kurozora.userauth')
            ->middleware('can:follow,user');

        Route::get('/{user}/followers', [FollowingController::class, 'getFollowers'])
            ->middleware('kurozora.userauth');

        Route::get('/{user}/following', [FollowingController::class, 'getFollowing'])
            ->middleware('kurozora.userauth');

        Route::get('/{user}/library', [LibraryController::class, 'getLibrary'])
            ->middleware('kurozora.userauth');

        Route::post('/{user}/library', [LibraryController::class, 'addLibrary'])
            ->middleware('kurozora.userauth');

        Route::post('/{user}/library/delete', [LibraryController::class, 'delLibrary'])
            ->middleware('kurozora.userauth');

        Route::post('/{user}/authenticate-channel', [UserController::class, 'authenticateChannel'])
            ->middleware('kurozora.userauth')
            ->middleware('can:authenticate_pusher_channel,user');

        Route::get('/{user}/profile', [UserController::class, 'profile'])
            ->middleware('kurozora.userauth');

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

        Route::post('/update', [UserNotificationController::class, 'update'])
            ->middleware('kurozora.userauth');
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
        Route::get('/search', [AnimeController::class, 'search'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{anime}', [AnimeController::class, 'view'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{anime}/actors', [AnimeController::class, 'actorsAnime']);

        Route::get('/{anime}/seasons', [AnimeController::class, 'seasonsAnime']);

        Route::post('/{anime}/rate', [AnimeController::class, 'rateAnime'])
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/anime-seasons')->group(function() {
        Route::get('/{season}', [AnimeSeasonController::class, 'details']);

        Route::get('/{season}/episodes', [AnimeSeasonController::class, 'episodes'])
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/anime-episodes')->group(function() {
        Route::post('/{episode}/watched', [AnimeEpisodeController::class, 'watched'])
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/genres')->group(function() {
        Route::get('/', [GenreController::class, 'overview']);

        Route::get('/{genre}', [GenreController::class, 'details']);
    });

    Route::prefix('/forum-sections')->group(function() {
        Route::get('/', [ForumSectionController::class, 'overview']);

        Route::get('/{section}', [ForumSectionController::class, 'details']);

        Route::get('/{section}/threads', [ForumSectionController::class, 'threads'])
            ->middleware('kurozora.userauth:optional');

        Route::post('/{section}/threads', [ForumSectionController::class, 'postThread'])
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/forum-threads')->group(function() {
        Route::get('/search', [ForumThreadController::class, 'search'])
            ->middleware('kurozora.userauth:optional');

        Route::get('/{thread}', [ForumThreadController::class, 'threadInfo']);

        Route::post('/{thread}/vote', [ForumThreadController::class, 'vote'])
            ->middleware('kurozora.userauth');

        Route::get('/{thread}/replies', [ForumThreadController::class, 'replies'])
            ->middleware('kurozora.userauth:optional');

        Route::post('/{thread}/replies', [ForumThreadController::class, 'postReply'])
            ->middleware('kurozora.userauth');

        Route::post('/{thread}/lock', [ForumThreadController::class, 'lock'])
            ->middleware('kurozora.userauth')
            ->middleware('can:lock_thread,thread');
    });

    Route::prefix('/forum-replies')->group(function() {
        Route::post('/{reply}/vote', [ForumReplyController::class, 'vote'])
            ->middleware('kurozora.userauth');
    });

    Route::prefix('/themes')->group(function() {
        Route::get('/', [AppThemeController::class, 'overview']);

        Route::get('/{theme}/download', [AppThemeController::class, 'download'])
            ->name('themes.download');
    });

    Route::get('/privacy-policy', [MiscController::class, 'getPrivacyPolicy']);
});
