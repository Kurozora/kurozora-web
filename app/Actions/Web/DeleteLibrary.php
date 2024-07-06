<?php

namespace App\Actions\Web;

use App\Contracts\DeletesLibraries;
use App\Enums\UserLibraryKind;
use App\Models\Anime;
use App\Models\Game;
use App\Models\Manga;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class DeleteLibrary implements DeletesLibraries
{
    /**
     * Delete the given user.
     *
     * @param User  $user
     * @param array $input
     *
     * @return void
     */
    public function delete(User $user, array $input): void
    {
        Validator::make($input, [
            'library' => ['required', 'integer', 'in:' . implode(',', UserLibraryKind::getValues())],
        ])->validateWithBag('deleteUserLibrary');

        // Get the library type
        $libraryKind = UserLibraryKind::fromValue((int) $input['library']);
        $type = match ($libraryKind->value) {
            UserLibraryKind::Anime => Anime::class,
            UserLibraryKind::Manga => Manga::class,
            UserLibraryKind::Game => Game::class
        };

        $user->clearLibrary($type);
        $user->clearFavorites($type);
        $user->clearReminders($type);
        $user->clearRatings($type);
    }
}
