<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SignUpRequest;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Routing\Redirector;
use Session;
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

        Session::flash('success', __('Account created successfully! Please check your email for confirmation.'));

        Auth::login($newUser);

        return $this->loginPipeline($request)->then(function () {
            return redirect()->intended();
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param SignUpRequest $request
     * @return Pipeline
     */
    protected function loginPipeline(SignUpRequest $request): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            PrepareAuthenticatedSession::class,
        ]));
    }
}
