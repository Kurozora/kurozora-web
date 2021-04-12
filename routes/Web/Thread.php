<?php

namespace App\Http\Controllers;

use App\Http\Livewire\Thread\Details;
use Illuminate\Support\Facades\Route;

Route::prefix('/thread')
    ->name('thread')
    ->group(function() {
        Route::get('/{thread}', Details::class)
            ->name('.details');
    });
