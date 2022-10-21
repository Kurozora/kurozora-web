<?php

use App\Http\Controllers\Web\Profile\AnimeLibraryController;
use App\Http\Controllers\Web\UserProfileController;
use App\Http\Livewire\Profile\Library\Anime\Favorites as FavoriteAnime;
use App\Http\Livewire\Profile\Library\Anime\Index as AnimeLibrary;
use App\Http\Livewire\Profile\Details;

Route::prefix('/profile')
    ->name('profile')
    ->group(function () {
        Route::get('/{user}', Details::class)
            ->name('.details');

        Route::get('/{user}/anime', AnimeLibrary::class)
            ->name('.anime-library');

        Route::get('/{user}/anime/favorites', FavoriteAnime::class)
            ->name('.favorite-anime');
    });

 Route::get('/animelist/{user?}', [AnimeLibraryController::class, 'index'])
    ->name('animelist');

Route::get('/settings', [UserProfileController::class, 'settings'])
    ->middleware(['auth', 'verified'])
    ->name('profile.settings');
