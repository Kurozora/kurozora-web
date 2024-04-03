<?php

namespace App\Http\Controllers\Web\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\GetGameLibraryRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class GameLibraryController extends Controller
{
    /**
     * Sends the user to the game library overview.
     *
     * @param GetGameLibraryRequest $request
     * @param User|null $user
     * @return RedirectResponse
     */
    public function index(GetGameLibraryRequest $request, ?User $user)
    {
        $data = $request->validated();
        $user = $user->id ? $user : auth()->user();
        $data['user'] = $user;

        if (empty($user)) {
            $request->session()->put('url.intended', route('gamelist'));
            return to_route('sign-in');
        }

        return to_route('profile.games.library', $data);
    }
}
