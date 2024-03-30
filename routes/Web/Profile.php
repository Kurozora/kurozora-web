<?php

use App\Http\Controllers\Web\MeController;
use App\Http\Controllers\Web\Profile\AnimeLibraryController;
use App\Http\Controllers\Web\Profile\GameLibraryController;
use App\Http\Controllers\Web\Profile\MangaLibraryController;
use App\Http\Controllers\Web\UserProfileController;
use App\Http\Livewire\Profile\Details;
use App\Http\Livewire\Profile\Library\Anime\Favorites as FavoriteAnime;
use App\Http\Livewire\Profile\Library\Anime\Index as AnimeLibrary;
use App\Http\Livewire\Profile\Library\Game\Favorites as FavoriteGame;
use App\Http\Livewire\Profile\Library\Game\Index as GameLibrary;
use App\Http\Livewire\Profile\Library\Manga\Favorites as FavoriteManga;
use App\Http\Livewire\Profile\Library\Manga\Index as MangaLibrary;

Route::prefix('/profile')
    ->name('profile')
    ->group(function () {
        Route::get('/', [MeController::class, 'index'])
            ->middleware(['auth'])
            ->name('.index');

        Route::get('/{user}', Details::class)
            ->name('.details');

        Route::get('/{user}/anime', AnimeLibrary::class)
            ->name('.anime-library');

        Route::get('/{user}/manga', MangaLibrary::class)
            ->name('.manga-library');

        Route::get('/{user}/games', GameLibrary::class)
            ->name('.games-library');

        Route::get('/{user}/anime/favorites', FavoriteAnime::class)
            ->name('.favorite-anime');

        Route::get('/{user}/manga/favorites', FavoriteManga::class)
            ->name('.favorite-manga');

        Route::get('/{user}/games/favorites', FavoriteGame::class)
            ->name('.favorite-games');
    });

 Route::get('/animelist/{user?}', [AnimeLibraryController::class, 'index'])
    ->name('animelist');

 Route::get('/mangalist/{user?}', [MangaLibraryController::class, 'index'])
    ->name('mangalist');

 Route::get('/gamelist/{user?}', [GameLibraryController::class, 'index'])
    ->name('gamelist');

Route::get('/settings', [UserProfileController::class, 'settings'])
    ->middleware(['auth', 'verified'])
    ->name('profile.settings');
