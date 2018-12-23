<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.home');
});

// Confirm email
Route::get('/confirmation/{confirmation_id}', 'UserController@confirmEmail');

// Reset password
Route::get('/reset/{reset_token}', 'UserController@resetPasswordPage');