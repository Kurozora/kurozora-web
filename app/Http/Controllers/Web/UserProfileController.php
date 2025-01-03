<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Show the user's profile settings.
     *
     * @param Request $request
     * @param User    $user
     *
     * @note Livewire always instantiates an instance of
     *       optional models, so making `users` optional
     *       is the same as making it required.
     *
     * @return Application|Factory|RedirectResponse|View
     */
    public function settings(Request $request, User $user): Application|Factory|RedirectResponse|View
    {
        // Redirect to main settings if user isn't allowed to view
        // the specified user's settings, or if the user views their
        // own settings through the `profile.settings.user` route.
        if ($user->id !== null && $request->user()->cannot('view_settings', $user)) {
            return redirect()->route('profile.settings');
        }

        return view('profile.settings', [
            'request' => $request,
            'user' => $user->id !== null ? $user : $request->user(),
        ]);
    }
}
