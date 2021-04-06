<?php

namespace App\Http\Controllers;

use App\Http\Livewire\Anime\Details;
use Illuminate\Support\Facades\Route;

Route::prefix('/anime')
    ->name('anime')
    ->group(function() {
        Route::get('/{anime}', Details::class)
            ->name('.details');
    });
