<?php

namespace App\Http\Controllers\API\v1;

use App\Contracts\Web\Auth\TwoFactorAuthenticationProvider;
use App\Helpers\JSONResult;
use App\Http\Requests\TwoFactorChallengeRequest;
use App\Http\Resources\UserResource;
use App\Models\PersonalAccessToken;
use App\Models\TwoFactorChallenge;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use Psr\SimpleCache\InvalidArgumentException;

class TwoFactorChallengeController
{
    /**
     * Resolve a two-factor challenge by exchanging a TOTP or recovery code for a Sanctum access token.
     *
     * @param TwoFactorChallengeRequest $request
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws ValidationException
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     * @throws InvalidArgumentException
     */
    public function create(TwoFactorChallengeRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Look up the challenge. A null result covers unknown, expired, or
        // pruned tokens. They are all equally invalid from the client's view.
        $challenge = TwoFactorChallenge::findValid($data['challenge_token']);

        if ($challenge === null || $challenge->isExhausted()) {
            throw new AuthenticationException(__('Your verification session has expired. Please sign in again.'));
        }

        // Eager-load the same relations as `AccessTokenController::create` so
        // the response shape is identical between the 2FA and non-2FA paths.
        $user = User::query()
            ->where('uuid', $challenge->user_id)
            ->with([
                'badges' => function ($query) {
                    $query->with(['media']);
                },
                'media',
                'tokens' => function ($query) {
                    $query->orderBy('last_used_at', 'desc')
                        ->limit(1);
                },
                'sessions' => function ($query) {
                    $query->orderBy('last_activity', 'desc')
                        ->limit(1);
                },
            ])
            ->withCount(['followers', 'followedModels as following_count', 'mediaRatings'])
            ->first();

        if ($user === null) {
            throw new AuthenticationException(__('Your verification session has expired. Please sign in again.'));
        }

        $isOtpFlow = !empty($data['otp']);
        $usedRecoveryCode = null;

        if ($isOtpFlow) {
            $isValid = app(TwoFactorAuthenticationProvider::class)->verify(
                decrypt($user->two_factor_secret),
                $data['otp']
            );
        } else {
            $usedRecoveryCode = collect($user->recoveryCodes())->first(function ($code) use ($data) {
                return hash_equals((string) $code, (string) $data['recovery_code']);
            });
            $isValid = $usedRecoveryCode !== null;
        }

        if (!$isValid) {
            $challenge->incrementAttempts();

            if ($challenge->isExhausted()) {
                $challenge->invalidate();
            }

            throw ValidationException::withMessages([
                'code' => __('The verification code is invalid. Please try again.'),
            ]);
        }

        // Single-use challenge: invalidate before issuing the token so a
        // race that submits twice cannot produce two tokens.
        $challenge->invalidate();

        if ($usedRecoveryCode !== null) {
            $user->replaceRecoveryCode($usedRecoveryCode);
        }

        $platformData = $challenge->platform_data ?? [];

        $newToken = $user->createToken($user->username . '’s ' . ($platformData['device_model'] ?? 'device'));
        /** @var PersonalAccessToken $personalAccessToken */
        $personalAccessToken = $newToken->accessToken;

        $user->createSessionAttributes($personalAccessToken, $platformData, true);

        return JSONResult::success([
            'data' => [
                UserResource::make($user)->includingAccessToken($personalAccessToken)
            ],
            'authenticationToken' => $newToken->plainTextToken
        ]);
    }
}
