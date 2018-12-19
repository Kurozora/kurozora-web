<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group([/*'middleware' => ['kurozora.useragent'],*/ 'prefix' => 'v1'], function () {
    Route::post('/users', 'UserController@register');

    Route::prefix('/user')->group(function() {
        Route::post('/login', 'UserController@login');
        Route::post('/logout', 'UserController@logout')->middleware('kurozora.userauth');
        Route::post('/reset_password', 'UserController@resetPassword');
        Route::post('/get_sessions', 'UserController@getSessions')->middleware('kurozora.userauth');
        Route::post('/get_library', 'UserController@getLibrary')->middleware('kurozora.userauth');
        Route::post('/add_library', 'UserController@addLibrary')->middleware('kurozora.userauth');
        Route::post('/remove_library', 'UserController@removeLibrary')->middleware('kurozora.userauth');
        Route::post('/delete_session', 'UserController@deleteSession')->middleware('kurozora.userauth');
        Route::post('/update_account', 'UserController@updateAccount');
        Route::post('/get_notifications', 'UserController@getNotifications')->middleware('kurozora.userauth');
        Route::post('/authenticate_channel', 'UserController@authenticateChannel')->middleware('kurozora.userauth');

        Route::post('/{id}/profile', 'UserController@profile')->where('id', '[0-9]*')->middleware('kurozora.userauth');
    });

    Route::prefix('/session')->group(function() {
        Route::post('/validate', 'SessionController@validateSession');
    });

    Route::prefix('/anime')->group(function() {
        Route::get('/explore', 'AnimeController@exploreAnime');

        Route::post('/{id}/details', 'AnimeController@detailsAnime')->where('id', '[0-9]*')->middleware('kurozora.userauth');
        Route::get('/{id}/actors', 'AnimeController@actorsAnime')->where('id', '[0-9]*');
        Route::get('/{id}/seasons', 'AnimeController@seasonsAnime')->where('id', '[0-9]*');
        Route::get('/{id}/episodes', 'AnimeController@episodesAnime')->where('id', '[0-9]*');
        Route::post('/{id}/rate', 'AnimeController@rateAnime')->where('id', '[0-9]*')->middleware('kurozora.userauth');
    });

    Route::prefix('/forum')->group(function() {
        Route::get('/get_sections', 'ForumController@getSections');
        Route::post('/get_threads', 'ForumController@getThreads');
        Route::post('/get_thread', 'ForumController@getThread');
        Route::post('/vote_thread', 'ForumController@voteThread')->middleware('kurozora.userauth');
        Route::post('/post_thread', 'ForumController@postThread')->middleware('kurozora.userauth');
        Route::post('/post_reply', 'ForumController@postReply')->middleware('kurozora.userauth');
    });

    Route::prefix('/misc')->group(function() {
        Route::get('/get_privacy_policy', 'MiscController@getPrivacyPolicy');
    });
});