<?php

namespace App\Http\Controllers\Web;

use App\Actions\Web\Auth\AttemptToAuthenticate;
use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Actions\Web\Auth\RedirectIfHasLocalLibrary;
use App\Enums\MediaCollection;
use App\Http\Controllers\Controller;
use App\Http\Requests\Web\SignUpRequest;
use App\Models\User;
use Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
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
        $data = $request->validated();

        // Create the user
        $newUser = User::create([
            'username' => $data['nickname'] ?? $data['username'] ?? str()->random(8),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'can_change_username' => false,
            'tv_rating' => 4
        ]);

        if ($request->hasFile('profileImage') &&
            $request->file('profileImage')->isValid()) {
            // Save the uploaded profile image
            $newUser->updateImageMedia(MediaCollection::Profile(), $request->file('profileImage'));
        }

        event(new Registered($newUser));

        return $this->signInPipeline($request)->then(function () {
            session()->flash('success', __('Account created successfully! Please check your email for confirmation.'));

            return redirect()->intended();
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param SignUpRequest $request
     *
     * @return Pipeline
     */
    protected function signInPipeline(SignUpRequest $request): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
            RedirectIfHasLocalLibrary::class,
        ]));
    }
}
