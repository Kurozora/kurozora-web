<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.home');
});

Route::get('/go-test', function () {
    return view('website.test');
});

// Confirm email
Route::get('/confirmation/{confirmation_id}', 'UserController@confirmEmail');

// Reset password
Route::get('/reset/{reset_token}', 'UserController@resetPasswordPage');