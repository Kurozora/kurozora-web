<?php

use App\Http\Livewire\Home;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get(RouteServiceProvider::HOME, Home::class)
    ->name('home');

// Authentication routes
require 'Web/Authentication.php';

// Landing pages
require 'Web/Anime.php';
require 'Web/Episodes.php';
require 'Web/Profile.php';
require 'Web/Seasons.php';
require 'Web/Theme.php';

// Legal pages
require 'Web/Legal.php';
