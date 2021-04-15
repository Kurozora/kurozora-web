<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserProfileController extends Controller
{
    /**
     * Show the user's profile settings.
     *
     * @param Request $request
     * @return Application|Factory|View
     */
    public function settings(Request $request): Application|Factory|View
    {
        return view('profile.settings', [
            'request' => $request,
            'user' => $request->user(),
        ]);
    }
}
