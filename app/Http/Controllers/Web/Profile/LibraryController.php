<?php

namespace App\Http\Controllers\Web\Profile;

use App\Enums\UserLibraryKind;
use App\Http\Controllers\Controller;
use App\Http\Requests\GetProfileLibraryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class LibraryController extends Controller
{
    /**
     * Sends the user to the library overview for the active kind.
     *
     * @param GetProfileLibraryRequest $request
     * @param int $kind
     * @param User|null $user
     * @return RedirectResponse
     */
    public function index(GetProfileLibraryRequest $request, int $kind, ?User $user)
    {
        $data = $request->validated();
        $user = $user->id ? $user : auth()->user();
        $data['user'] = $user;

        if (empty($user)) {
            $intendedRoute = match ($kind) {
                UserLibraryKind::Anime => route('animelist'),
                UserLibraryKind::Manga => route('mangalist'),
                UserLibraryKind::Game  => route('gamelist'),
            };
            $request->session()->put('url.intended', $intendedRoute);
            return to_route('sign-in');
        }

        return match ($kind) {
            UserLibraryKind::Anime => to_route('profile.anime.library', $data),
            UserLibraryKind::Manga => to_route('profile.manga.library', $data),
            UserLibraryKind::Game  => to_route('profile.games.library', $data),
        };
    }
}
