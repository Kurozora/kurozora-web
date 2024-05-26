<?php

namespace Tests\API;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class SignInTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can sign in.
     *
     * @return void
     */
    #[Test]
    function a_user_can_sign_in(): void
    {
        $this->json('POST', 'v1/users/signin', [
            'email'             => $this->user->email,
            'password'          => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();

        // Check whether a session was created for the user
        $this->assertEquals(1, $this->user->tokens()->count());
    }

    /**
     * Test if a user cannot sign in with an incorrect password.
     *
     * @return void
     */
    #[Test]
    function a_user_cannot_sign_in_with_an_incorrect_password(): void
    {
        $this->json('POST', 'v1/users/signin', [
            'email'     => $this->user->email,
            'password'  => $this->userPassword . 'invalid',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertUnsuccessfulAPIResponse();

        // Check that no session was created
        $this->assertEquals(0, $this->user->sessions()->count());
    }

    /**
     * Test if a user cannot sign in with an unknown email.
     *
     * @return void
     */
    #[Test]
    function a_user_cannot_sign_in_with_an_unknown_email(): void
    {
        $this->json('POST', 'v1/users/signin', [
            'email'     => 'invalidemail@example.com',
            'password'  => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can only attempt to sign in 3 times per 5 minutes.
     *
     * @return void
     */
    #[Test]
    function a_user_can_only_attempt_to_sign_in_3_times_per_5_minutes(): void
    {
        // Make 3 sign in attempts with wrong password
        for($i = 0; $i < 3; $i++)
            $this->json('POST', 'v1/users/signin', [
                'email'     => $this->user->email,
                'password'  => $this->userPassword . 'invalid',
                'platform'          => 'iOS',
                'platform_version'  => '13.4',
                'device_vendor'     => 'Apple',
                'device_model'      => 'iPhone 11 Pro Max'
            ])->assertUnsuccessfulAPIResponse();

        // 4th attempt with correct password should fail
        $this->json('POST', 'v1/users/signin', [
            'email'     => $this->user->email,
            'password'  => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertUnsuccessfulAPIResponse();

        // Time travel to the future
        Carbon::setTestNow(now()->addMinutes(6));

        // Should now be able to sign in, because cooldown is over
        $this->json('POST', 'v1/users/signin', [
            'email'     => $this->user->email,
            'password'  => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();
    }

    /**
     * Test if a user receives a notification when someone logs into their account.
     *
     * @return void
     */
    #[Test]
    function a_user_receives_a_notification_when_someone_logs_into_their_account(): void
    {
        $this->json('POST', 'v1/users/signin', [
            'email'     => $this->user->email,
            'password'  => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();

        // Check whether the user now has one notification
        $this->assertEquals(1, $this->user->notifications()->count());
    }
}
