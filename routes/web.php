<?php

use App\Http\Controllers\Web\Misc\HealthCheckController;
use App\Http\Livewire\Home;
use App\Http\Livewire\Welcome;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Route;

Route::get(RouteServiceProvider::HOME, Home::class)
    ->name('home');

Route::get('welcome', Welcome::class)
    ->name('welcome');

Route::get('health-check', [HealthCheckController::class, 'index'])
    ->name('misc.health-check');

// Authentication routes
require 'Web/Authentication.php';

// Landing pages
require 'Web/Anime.php';
require 'Web/Characters.php';
require 'Web/Episodes.php';
require 'Web/Explore.php';
require 'Web/Genres.php';
require 'Web/Library.php';
require 'Web/Me.php';
require 'Web/People.php';
require 'Web/Profile.php';
require 'Web/Seasons.php';
require 'Web/Studios.php';
require 'Web/Theme.php';

// Knowledge Base
require 'Web/Knowledge Base.php';

// Misc pages
require 'Web/Misc.php';

// Legal pages
require 'Web/Legal.php';
