<?php

namespace Tests\API;

use App\Notifications\ResetPassword;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Notification;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class ResetPasswordTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Set up the test.
     *
     * @return void
     */
    function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    /**
     * Test if a password reset cannot be requested with an invalid email format.
     *
     * @return void
     * @test
     */
    function password_reset_cannot_be_requested_with_an_invalid_email_address_format(): void
    {
        $response = $this->json('POST', 'v1/users/reset-password', [
            'email' => 'not_an_email'
        ]);
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a password reset can be requested with a known (registered) email address.
     *
     * @return void
     * @test
     */
    function password_request_can_be_requested_with_known_email_address(): void
    {
        $response = $this->json('POST', 'v1/users/reset-password', [
            'email' => $this->user->email
        ]);
        $response->assertSuccessfulAPIResponse();
    }

    /**
     * Test if the password reset email is sent to a known email address.
     *
     * @return void
     * @test
     * @throws Exception
     */
    function password_reset_email_is_sent_to_known_email_address(): void
    {
        // Attempt to request password reset
        $response = $this->json('POST', 'v1/users/reset-password', [
            'email' => $this->user->email
        ]);
        $response->assertSuccessfulAPIResponse();

        Notification::assertSentTo($this->user, ResetPassword::class);
    }

    /**
     * Test if a password reset can be requested with an unknown email.
     *
     * This needs to work like this, because otherwise we would give away ..
     * .. which emails are registered and which are not.
     *
     * @return void
     * @test
     */
    function password_request_can_be_requested_with_unknown_email_address(): void
    {
        $response = $this->json('POST', 'v1/users/reset-password', [
            'email' => 'unknown@kurozora.app'
        ]);
        $response->assertSuccessfulAPIResponse();
    }

    /**
     * Test if the password reset email is not sent to an unknown email address.
     *
     * @return void
     * @test
     * @throws Exception
     */
    function password_reset_email_is_not_sent_to_unknown_email_address(): void
    {
        // Attempt to request password reset
        $response = $this->json('POST', 'v1/users/reset-password', [
            'email' => 'unknown@kurozora.app'
        ]);
        $response->assertSuccessfulAPIResponse();

        Notification::assertNothingSent();
    }
}
