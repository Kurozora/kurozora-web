<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('website.home');
})->name('home');

// Landing pages
require 'Web/Anime.php';
require 'Web/Profile.php';
require 'Web/Theme.php';
require 'Web/Thread.php';

// Legal pages
require 'Web/Legal.php';

// Confirm email
Route::get('/confirmation/{confirmation_id}', [UserController::class, 'confirmEmail']);

// Reset password
Route::get('/reset/{token}', [UserController::class, 'resetPasswordPage'])
    ->name('reset-password');
