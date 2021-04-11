<?php

use App\Http\Controllers\UserController;
use App\Http\Livewire\Email\Verification;
use App\Http\Livewire\Home;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)
    ->name('home');

// Verify email
Route::get('/verification/{verificationID}', Verification::class);

// Reset password
Route::get('/reset/{token}', [UserController::class, 'resetPasswordPage'])
    ->name('reset-password');

// Landing pages
require 'Web/Anime.php';
require 'Web/Profile.php';
require 'Web/Theme.php';
require 'Web/Thread.php';

// Legal pages
require 'Web/Legal.php';
