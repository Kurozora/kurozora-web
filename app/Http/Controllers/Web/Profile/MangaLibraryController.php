<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetMangaLibraryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class MangaLibraryController extends Controller
{
    /**
     * Sends the user to the manga library overview.
     *
     * @param GetMangaLibraryRequest $request
     * @param User|null $user
     * @return RedirectResponse
     */
    public function index(GetMangaLibraryRequest $request, ?User $user)
    {
        $data = $request->validated();
        $data['user'] = $user->id ? $user : auth()->user();

        return to_route('profile.manga-library', $data);
    }
}
