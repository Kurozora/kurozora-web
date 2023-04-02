<?php

namespace App\Http\Controllers\Web\Auth;

use App\Actions\Web\Auth\PrepareAuthenticatedSession;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;

class SignInWithAppleController extends Controller
{
    /**
     * Show the sign in view.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws Exception
     */
    public function signIn(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return Socialite::driver('apple')->redirect();
    }

    /**
     * Handle the providers callback
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function callback(Request $request): RedirectResponse
    {
        $socialiteUser = Socialite::driver('apple')->user();

        // Sign in user
        return $this->signInPipeline($request, $socialiteUser)->then(function () {
            return redirect()->intended();
        });
    }

    /**
     * Get the authentication pipeline instance.
     *
     * @param Request $request
     * @param SocialiteUser $socialiteUser
     * @return Pipeline
     */
    protected function signInPipeline(Request $request, SocialiteUser $socialiteUser): Pipeline
    {
        return (new Pipeline(app()))->send($request)->through(array_filter([
            $this->authenticateUser($socialiteUser),
            PrepareAuthenticatedSession::class,
        ]));
    }

    /**
     * Find user account if it exists.
     *
     * @param SocialiteUser $socialiteUser
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
        auth()->login($user);
    }

    /**
     * Creates a new user from the given payload.
     *
     * @param SocialiteUser $socialiteUser
     * @return User|null
     */
    protected function signUpUser(SocialiteUser $socialiteUser): ?User
    {
        return User::create([
            'username' => $socialiteUser->getName(),
            'email' => $socialiteUser->getEmail(),
            'siwa_id' => $socialiteUser->getId(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(30)),
            'can_change_username' => true,
            'tv_rating' => 4
        ]);
    }
}
