<?php

use App\Http\Livewire\Email\Verification;
use App\Http\Livewire\Home;
use App\Http\Livewire\Misc\ResetPassword;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get(RouteServiceProvider::HOME, Home::class)
    ->name('home');

// Authentication routes
require 'Web/Authentication.php';

// Verify email
Route::get('/verify/{verificationID}', Verification::class)
    ->name('email.verify');

// Reset password
Route::get('/reset-password/{token}', ResetPassword::class)
    ->name('password.reset');


// Landing pages
require 'Web/Anime.php';
require 'Web/Profile.php';
require 'Web/Theme.php';
require 'Web/Thread.php';

// Legal pages
require 'Web/Legal.php';
