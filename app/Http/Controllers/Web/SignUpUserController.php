<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SignUpRequest;
use App\Models\User;
use Auth;
use Browser;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Throwable;

class SignUpUserController extends Controller
{
    /**
     * Show the registration view.
     *
     * @param Request $request
     *
     * @return Application|Factory|View
     */
    public function create(Request $request): Application|Factory|View
    {
        return view('auth.sign-up');
    }

    /**
     * Create a new registered user.
     *
     * @param SignUpRequest $request
     *
     * @return Application|RedirectResponse|Redirector
     * @throws Throwable
     */
    public function store(SignUpRequest $request): Application|RedirectResponse|Redirector
    {
        $data = $request->only(['username', 'email', 'password']);

        // Create the user
        $newUser = User::create([
            'username'              => $data['username'],
            'email'                 => $data['email'],
            'password'              => Hash::make($data['password']),
            'settings'              => [
                'can_change_username'   => false,
                'tv_rating'             => -1
            ]
        ]);

        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()) {
            // Save the uploaded profile image
            $newUser->updateProfileImage($request->file('profileImage'));
        }

        event(new Registered($newUser));

        Auth::login($newUser);

        $this->prepareAuthenticatedSession();

        return redirect('/');
    }

    /**
     * Prepares the authenticated session for the newly authenticated user.
     */
    protected function prepareAuthenticatedSession()
    {
        $browser = Browser::detect();

        Auth::user()->createSession([
            'platform'          => $browser->platformFamily(),
            'platform_version'  => $browser->platformVersion(),
            'device_vendor'     => $browser->deviceFamily(),
            'device_model'      => $browser->deviceModel(),
        ]);
    }
}
