<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetAnimeLibraryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AnimeLibraryController extends Controller
{
    /**
     * Sends the user to the anime library overview.
     *
     * @param GetAnimeLibraryRequest $request
     * @param User|null $user
     * @return RedirectResponse
     */
    public function index(GetAnimeLibraryRequest $request, ?User $user)
    {
        $data = $request->validated();
        $data['user'] = $user->id ? $user : auth()->user();

        return to_route('profile.anime-library', $data);
    }
}
