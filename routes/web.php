<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.home');
})->name('home');

// Privacy page
require 'Web/Legal.php';

// Landing pages
Route::get('/anime/{animeID}', [PageController::class, 'anime']);
Route::get('/profile/{userID}', [PageController::class, 'userProfile']);
Route::get('/thread/{threadID}', [PageController::class, 'thread']);

// Confirm email
Route::get('/confirmation/{confirmation_id}', [UserController::class, 'confirmEmail']);

// Reset password
Route::get('/reset/{token}', [UserController::class, 'resetPasswordPage'])
    ->name('reset-password');
