<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class MeController extends Controller
{
    /**
     * Returns the profile details for the authenticated user.
     *
     * @param Request $request
     * @return Application|Redirector|RedirectResponse
     */
    public function index(Request $request): Application|Redirector|RedirectResponse
    {
        return redirect(route('profile.details', ['user' => Auth::user()]));
    }
}
