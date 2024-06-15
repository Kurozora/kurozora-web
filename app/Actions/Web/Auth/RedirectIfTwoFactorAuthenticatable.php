<?php

namespace App\Actions\Web\Auth;

use App\Events\TwoFactorAuthenticationChallenged;
use App\Helpers\SignInRateLimiter;
use App\Models\User;
use App\Traits\Web\Auth\TwoFactorAuthenticatable;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfTwoFactorAuthenticatable
{
    /**
     * The login rate limiter instance.
     *
     * @var SignInRateLimiter
     */
    protected SignInRateLimiter $limiter;

    /**
     * Create a new controller instance.
     *
     * @param SignInRateLimiter $limiter
     *
     * @return void
     */
    public function __construct(SignInRateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param callable $next
     *
     * @return mixed
     */
    public function handle(Request $request, callable $next): mixed
    {
        $user = $this->validateCredentials($request);

        if (optional($user)->two_factor_secret &&
            !is_null(optional($user)->two_factor_confirmed_at) &&
            in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return $this->twoFactorChallengeResponse($request, $user);
        }

        return $next($request);
    }

    /**
     * Attempt to validate the incoming credentials.
     *
     * @param Request $request
     *
     * @return mixed
     */
    protected function validateCredentials(Request $request): mixed
    {
        return tap(User::where('email', $request->email)->first(), function ($user) use ($request) {
            if (!$user || !Hash::check($request->password, $user->password)) {
                $this->fireFailedEvent($request, $user);

                $this->throwFailedAuthenticationException($request);
            }
        });
    }

    /**
     * Throw a failed authentication validation exception.
     *
     * @param Request $request
     * @return void
     */
    protected function throwFailedAuthenticationException(Request $request): void
    {
        $this->limiter->increment($request);

        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    /**
     * Fire the failed authentication attempt event with the given arguments.
     *
     * @param Request $request
     * @param Authenticatable|null $user
     * @return void
     */
    protected function fireFailedEvent(Request $request, ?Authenticatable $user = null): void
    {
        event(new Failed('web', $user, [
            'email' => $request->email,
            'password' => $request->password,
        ]));
    }

    /**
     * Get the two-factor authentication enabled response.
     *
     * @param Request $request
     * @param User|null $user
     * @return Response
     */
    protected function twoFactorChallengeResponse(Request $request, ?User $user): Response
    {
        $request->session()->put([
            'sign-in.id' => $user->getKey(),
            'sign-in.remember' => $request->filled('remember'),
            'sign-in.has-local-library' => $request->filled('hasLocalLibrary'),
        ]);

        TwoFactorAuthenticationChallenged::dispatch($user);

        return $request->wantsJson()
            ? response()->json(['two_factor' => true])
            : redirect()->route('two-factor.sign-in');
    }
}
