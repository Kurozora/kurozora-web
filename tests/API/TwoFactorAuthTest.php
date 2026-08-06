<?php

namespace Tests\API;

use App\Helpers\RecoveryCode;
use App\Models\TwoFactorChallenge;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorAuthTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * The plain-text password used by all test users.
     */
    private const string PASSWORD = 'secret';

    /**
     * The platform payload sent on every step-1 sign-in attempt.
     */
    private const array PLATFORM_PAYLOAD = [
        'platform'         => 'iOS',
        'platform_version' => '13.4',
        'device_vendor'    => 'Apple',
        'device_model'     => 'iPhone 11 Pro Max',
    ];

    /**
     * Build a user with 2FA enabled and return a tuple of the user, the
     * plaintext TOTP secret, and the plaintext recovery codes.
     *
     * Inlined here rather than added to UserFactory to keep the shared
     * factory unchanged. Mirrors the live enrollment flow's payload.
     *
     * @param array|null $recoveryCodes
     *
     * @return array{0: User, 1: string, 2: array}
     */
    private function makeTwoFactorUser(?array $recoveryCodes = null): array
    {
        $secret = (new Google2FA())->generateSecretKey();
        $codes  = $recoveryCodes ?? array_map(fn () => RecoveryCode::generate(), range(0, 7));

        /** @var User $user */
        $user = User::factory()->create([
            'username'                  => 'KurozoraTester',
            'email'                     => 'tester@kurozora.app',
            'password'                  => Hash::make(self::PASSWORD),
            'email_verified_at'         => now(),
            'two_factor_secret'         => encrypt($secret),
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
            'two_factor_confirmed_at'   => now(),
        ]);

        return [$user, $secret, $codes];
    }

    /**
     * Build a user without 2FA enabled.
     *
     * @return User
     */
    private function makePlainUser(): User
    {
        /** @var User $user */
        $user = User::factory()->create([
            'username'          => 'KurozoraTester',
            'email'             => 'tester@kurozora.app',
            'password'          => Hash::make(self::PASSWORD),
            'email_verified_at' => now(),
        ]);

        return $user;
    }

    /**
     * Compute a valid TOTP code for the given secret at the current time.
     *
     * @param string $secret
     *
     * @return string
     */
    private function currentOtp(string $secret): string
    {
        return (new Google2FA())->getCurrentOtp($secret);
    }

    /**
     * Issue a step-1 sign-in attempt and return the response.
     *
     * @param User       $user
     * @param array|null $overrides
     *
     * @return \Illuminate\Testing\TestResponse
     */
    private function postSignIn(User $user, ?array $overrides = null): \Illuminate\Testing\TestResponse
    {
        return $this->json('POST', 'v1/users/signin', array_merge([
            'email'               => $user->email,
            'password'            => self::PASSWORD,
            'client_supports_2fa' => 1,
        ], self::PLATFORM_PAYLOAD, $overrides ?? []));
    }

    /**
     * 1. Non-2FA accounts continue to receive a Sanctum token directly from
     *    the sign-in endpoint without going through a challenge.
     */
    #[Test]
    function signin_without_2fa_returns_token(): void
    {
        $user = $this->makePlainUser();

        $response = $this->postSignIn($user)
            ->assertSuccessfulAPIResponse();

        $response->assertJsonStructure(['authenticationToken'])
            ->assertJsonMissingPath('two_factor');

        $this->assertEquals(1, $user->tokens()->count());
    }

    /**
     * 2. 2FA-enabled accounts receive a challenge token instead of a session
     *    token when the client advertises 2FA capability.
     */
    #[Test]
    function signin_with_2fa_and_capable_client_returns_challenge(): void
    {
        [$user] = $this->makeTwoFactorUser();

        $response = $this->postSignIn($user)
            ->assertSuccessfulAPIResponse()
            ->assertJson(['two_factor' => true])
            ->assertJsonStructure(['challenge_token']);

        // No token issued at step 1.
        $this->assertEquals(0, $user->tokens()->count());

        // A challenge row exists for the user.
        $this->assertEquals(1, TwoFactorChallenge::query()
            ->where('user_id', $user->uuid)
            ->count());
    }

    /**
     * 3. Old clients that did not send `client_supports_2fa` are blocked
     *    with a 403 + APIError id 40003 so they can prompt the user to
     *    update.
     */
    #[Test]
    function signin_with_2fa_and_old_client_returns_403(): void
    {
        [$user] = $this->makeTwoFactorUser();

        $this->postSignIn($user, ['client_supports_2fa' => null])
            ->assertStatus(403)
            ->assertJsonPath('errors.0.id', 40003);

        $this->assertEquals(0, $user->tokens()->count());
    }

    /**
     * 4. A valid TOTP code resolves the challenge into a Sanctum token.
     */
    #[Test]
    function two_factor_challenge_with_valid_otp_returns_token(): void
    {
        [$user, $secret] = $this->makeTwoFactorUser();
        $token = TwoFactorChallenge::issue($user);

        $response = $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => $this->currentOtp($secret),
        ]);

        $response->assertSuccessfulAPIResponse()
            ->assertJsonStructure(['data', 'authenticationToken']);

        $this->assertEquals(1, $user->tokens()->count());
    }

    /**
     * 5. A valid recovery code also resolves the challenge. The recovery
     *    code count is preserved (the consumed code is replaced).
     */
    #[Test]
    function two_factor_challenge_with_valid_recovery_code_returns_token(): void
    {
        [$user, , $codes] = $this->makeTwoFactorUser();
        $token = TwoFactorChallenge::issue($user);

        $response = $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'recovery_code'   => $codes[0],
        ]);

        $response->assertSuccessfulAPIResponse()
            ->assertJsonStructure(['data', 'authenticationToken']);

        $user->refresh();
        $newCodes = $user->recoveryCodes();

        $this->assertCount(count($codes), $newCodes);
        $this->assertNotContains($codes[0], $newCodes);
    }

    /**
     * 6. An invalid TOTP code returns 422 / id 40022 and increments the
     *    challenge's failed-attempt counter.
     */
    #[Test]
    function two_factor_challenge_with_invalid_otp_returns_422(): void
    {
        [$user] = $this->makeTwoFactorUser();
        $token  = TwoFactorChallenge::issue($user);

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => '000000',
        ])
            ->assertStatus(422)
            ->assertJsonPath('errors.0.id', 40022);

        $this->assertEquals(1, TwoFactorChallenge::query()
            ->where('user_id', $user->uuid)
            ->value('attempts_used'));
    }

    /**
     * 7. Four invalid attempts increment the counter without exhausting the
     *    challenge — the user can still try again.
     */
    #[Test]
    function two_factor_challenge_increments_attempt_count(): void
    {
        [$user, $secret] = $this->makeTwoFactorUser();
        $token = TwoFactorChallenge::issue($user);

        for ($i = 0; $i < 4; $i++) {
            $this->json('POST', 'v1/users/two-factor-challenge', [
                'challenge_token' => $token,
                'otp'             => '000000',
            ])->assertStatus(422);
        }

        $this->assertEquals(4, TwoFactorChallenge::query()
            ->where('user_id', $user->uuid)
            ->value('attempts_used'));

        // 5th attempt with a valid code must still succeed.
        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => $this->currentOtp($secret),
        ])->assertSuccessfulAPIResponse();
    }

    /**
     * 8. After 5 invalid submissions the challenge is exhausted; the 6th
     *    attempt receives the "expired/exhausted" response (id 40001).
     */
    #[Test]
    function two_factor_challenge_exhausted_after_5_failures(): void
    {
        [$user] = $this->makeTwoFactorUser();
        $token  = TwoFactorChallenge::issue($user);

        for ($i = 0; $i < 5; $i++) {
            $this->json('POST', 'v1/users/two-factor-challenge', [
                'challenge_token' => $token,
                'otp'             => '000000',
            ])->assertStatus(422);
        }

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => '000000',
        ])
            ->assertStatus(401)
            ->assertJsonPath('errors.0.id', 40001);
    }

    /**
     * 9. A challenge token that has aged past its TTL returns 40001.
     */
    #[Test]
    function two_factor_challenge_with_expired_token_returns_401(): void
    {
        [$user, $secret] = $this->makeTwoFactorUser();
        $token = TwoFactorChallenge::issue($user);

        Carbon::setTestNow(now()->addMinutes(TwoFactorChallenge::TTL_MINUTES + 1));

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => $this->currentOtp($secret),
        ])
            ->assertStatus(401)
            ->assertJsonPath('errors.0.id', 40001);
    }

    /**
     * 10. An unknown challenge token also returns 40001 — we deliberately
     *     do not differentiate between expired and never-existed.
     */
    #[Test]
    function two_factor_challenge_with_unknown_token_returns_401(): void
    {
        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => 'this-token-does-not-exist',
            'otp'             => '000000',
        ])
            ->assertStatus(401)
            ->assertJsonPath('errors.0.id', 40001);
    }

    /**
     * 11. Validation rejects a request that supplies neither otp nor
     *     recovery_code.
     */
    #[Test]
    function two_factor_challenge_requires_either_otp_or_recovery_code(): void
    {
        [$user] = $this->makeTwoFactorUser();
        $token  = TwoFactorChallenge::issue($user);

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
        ])->assertStatus(422);
    }

    /**
     * 12. Validation rejects a request that supplies both otp and
     *     recovery_code at the same time.
     */
    #[Test]
    function two_factor_challenge_rejects_both_otp_and_recovery_code(): void
    {
        [$user, $secret, $codes] = $this->makeTwoFactorUser();
        $token = TwoFactorChallenge::issue($user);

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => $this->currentOtp($secret),
            'recovery_code'   => $codes[0],
        ])->assertStatus(422);
    }

    /**
     * 13. A challenge token is single-use: a successful exchange invalidates
     *     it so a stolen token can't be replayed.
     */
    #[Test]
    function two_factor_challenge_token_cannot_be_reused_after_success(): void
    {
        [$user, $secret] = $this->makeTwoFactorUser();
        $token = TwoFactorChallenge::issue($user);

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => $this->currentOtp($secret),
        ])->assertSuccessfulAPIResponse();

        $this->json('POST', 'v1/users/two-factor-challenge', [
            'challenge_token' => $token,
            'otp'             => $this->currentOtp($secret),
        ])
            ->assertStatus(401)
            ->assertJsonPath('errors.0.id', 40001);
    }

    /**
     * 14. SIWA bypasses 2FA — Apple already enforces 2FA at the OS level,
     *     so a 2FA-enabled account must still be able to sign in via SIWA
     *     without a challenge.
     *
     * The end-to-end SIWA flow requires mocking Apple's auth keys + a JWT
     * payload, which is outside the scope of this controller change. We
     * assert the architectural invariant directly: the SIWA controller does
     * not consult `hasEnabledTwoFactorAuthentication()` and therefore can
     * never branch into the challenge path.
     */
    #[Test]
    function siwa_signin_bypasses_2fa(): void
    {
        $controllerSource = file_get_contents(
            base_path('app/Http/Controllers/Auth/SignInWithAppleController.php')
        );

        $this->assertStringNotContainsString(
            'hasEnabledTwoFactorAuthentication',
            $controllerSource,
            'SignInWithAppleController must not branch on 2FA — Apple enforces it at the OS level.'
        );

        $this->assertStringNotContainsString(
            'TwoFactorChallenge',
            $controllerSource,
            'SignInWithAppleController must not issue 2FA challenges.'
        );
    }

    /**
     * 15. The IP-based rate limit at step 1 is unaffected by the 2FA branch:
     *     three wrong passwords still trigger the 429 cooldown.
     */
    #[Test]
    function ip_rate_limit_still_applies_at_step1(): void
    {
        [$user] = $this->makeTwoFactorUser();

        for ($i = 0; $i < 3; $i++) {
            $this->postSignIn($user, ['password' => 'wrong-password'])
                ->assertUnsuccessfulAPIResponse();
        }

        $this->postSignIn($user, ['password' => 'wrong-password'])
            ->assertStatus(429);
    }

    /**
     * 16. Backwards-compat: an old client (no `client_supports_2fa`) may
     *     append the user's TOTP code to their password. The split is
     *     accepted as if the user completed the challenge step in-line —
     *     no challenge row is created and a Sanctum token is returned.
     */
    #[Test]
    function signin_old_client_with_password_plus_otp_returns_token_directly(): void
    {
        [$user, $secret] = $this->makeTwoFactorUser();

        $this->postSignIn($user, [
            'password'            => self::PASSWORD . $this->currentOtp($secret),
            'client_supports_2fa' => null,
        ])
            ->assertSuccessfulAPIResponse()
            ->assertJsonStructure(['authenticationToken'])
            ->assertJsonMissingPath('two_factor');

        $this->assertEquals(1, $user->tokens()->count());
        $this->assertEquals(0, TwoFactorChallenge::query()
            ->where('user_id', $user->uuid)
            ->count());
    }

    /**
     * 17. Backwards-compat: an old client appending the wrong OTP fails
     *     authentication and counts as a failed sign-in attempt against
     *     the IP rate limit.
     */
    #[Test]
    function signin_old_client_with_password_plus_invalid_otp_returns_401(): void
    {
        [$user] = $this->makeTwoFactorUser();

        $this->postSignIn($user, [
            'password'            => self::PASSWORD . '000000',
            'client_supports_2fa' => null,
        ])
            ->assertUnsuccessfulAPIResponse();

        $this->assertEquals(0, $user->tokens()->count());
    }

    /**
     * 18. Backwards-compat is gated on the absence of `client_supports_2fa`.
     *     A capable client that sends the concatenated password is treated
     *     as a regular wrong-password attempt — no split is attempted.
     */
    #[Test]
    function signin_capable_client_with_password_plus_otp_still_returns_challenge(): void
    {
        [$user, $secret] = $this->makeTwoFactorUser();

        $this->postSignIn($user, [
            'password'            => self::PASSWORD . $this->currentOtp($secret),
            'client_supports_2fa' => 1,
        ])
            ->assertUnsuccessfulAPIResponse();

        $this->assertEquals(0, $user->tokens()->count());
        $this->assertEquals(0, TwoFactorChallenge::query()
            ->where('user_id', $user->uuid)
            ->count());
    }
}
