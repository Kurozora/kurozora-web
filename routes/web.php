<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.home');
});

Route::get('/anime/{animeID}', [PageController::class, 'anime']);
Route::get('/profile/{userID}', [PageController::class, 'userProfile']);

// Confirm email
Route::get('/confirmation/{confirmation_id}', 'UserController@confirmEmail');

// Reset password
Route::get('/reset/{reset_token}', 'UserController@resetPasswordPage');