<?php

namespace App\Http\Controllers;

use App\Http\Livewire\Profile\Details;
use Illuminate\Support\Facades\Route;

Route::prefix('/profile')
    ->name('profile')
    ->group(function() {
        Route::get('/{user}', Details::class)
            ->name('.details');
    });
