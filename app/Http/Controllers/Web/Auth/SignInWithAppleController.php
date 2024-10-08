<?php

namespace App\Http\Controllers\Web\Auth;

use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Actions\Web\Auth\RedirectIfHasLocalLibrary;
use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Random\RandomException;

class SignInWithAppleController extends Controller
{
    /**
     * Show the sign in view.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function signIn(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        session(['hasLocalLibrary' => $request->boolean('hasLocalLibrary')]);

        return Socialite::driver('apple')
            ->redirect();
    }

    /**
     * Handle the providers callback
     *
     * @param Request $request
     *
     * @return RedirectResponse
     * @throws RandomException
     */
    public function callback(Request $request): RedirectResponse
    {
        $request->merge(['hasLocalLibrary' => session('hasLocalLibrary')]);
        session()->forget(['hasLocalLibrary']);

        $socialiteUser = Socialite::driver('apple')->user();

        // Sign in user
        return $this->signInPipeline($request, $socialiteUser)->then(function () {
            return redirect()->intended();
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param Request       $request
     * @param SocialiteUser $socialiteUser
     *
     * @return Pipeline
     * @throws RandomException
     */
    protected function signInPipeline(Request $request, SocialiteUser $socialiteUser): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            $this->authenticateUser($socialiteUser),
            PrepareAuthenticatedSession::class,
            RedirectIfHasLocalLibrary::class,
        ]));
    }

    /**
     * Find user account if it exists.
     *
     * @param SocialiteUser $socialiteUser
     *
     * @throws RandomException
     */
    protected function authenticateUser(SocialiteUser $socialiteUser)
    {
        // Find the user by their email.
        $email = $socialiteUser->getEmail();
        $user = User::firstWhere('email', $email);

        // In case email wasn't returned by Apple, use SiwA ID to find the user.
        if (empty($user)) {
            $subject = $socialiteUser->getId();
            $user = User::firstWhere('siwa_id', $subject);
        }

        // If user is empty then register a new account because they don't have one.
        if (empty($user)) {
            $user = $this->signUpUser($socialiteUser);
        }

        // Sign in the user.
        auth()->login($user, true);
    }

    /**
     * Creates a new user from the given payload.
     *
     * @param SocialiteUser $socialiteUser
     *
     * @return User|null
     * @throws RandomException
     */
    protected function signUpUser(SocialiteUser $socialiteUser): ?User
    {
        return User::create([
            'username' => $socialiteUser->getName() ?? bin2hex(random_bytes(20)),
            'email' => $socialiteUser->getEmail(),
            'siwa_id' => $socialiteUser->getId(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(30)),
            'can_change_username' => true,
            'tv_rating' => 4
        ]);
    }
}
