<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('/v1')->group(function() {
    Route::prefix('/user')->group(function() {
        Route::post('/register', 'UserController@register');
        Route::post('/login', 'UserController@login');
        Route::post('/logout', 'UserController@logout');
        Route::post('/get_sessions', 'UserController@getSessions');
        Route::post('/delete_session', 'UserController@deleteSession');
    });

    Route::prefix('/session')->group(function() {
        Route::post('/validate', 'SessionController@validateSession');
    });

    Route::prefix('/anime')->group(function() {
        Route::get('/explore', 'AnimeController@exploreAnime');
        Route::get('/{id}/details', 'AnimeController@detailsAnime')->where('id', '[0-9]*');
    });
});