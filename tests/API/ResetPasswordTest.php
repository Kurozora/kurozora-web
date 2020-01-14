<?php

namespace Tests\API;

use App\Mail\ResetPassword;
use App\PasswordReset;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\API\Traits\ProvidesTestUser;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a password reset cannot be requested with an invalid email format.
     *
     * @return void
     * @test
     */
    function password_reset_cannot_be_requested_with_an_invalid_email_address_format() {
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => 'not_an_email'
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a password reset can be requested with a known (registered) email address.
     *
     * @return void
     * @test
     */
    function password_request_can_be_requested_with_known_email_address() {
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ])->assertSuccessfulAPIResponse();
    }

    /**
     * Test if the password reset email is sent to a known email address.
     *
     * @return void
     * @test
     */
    function password_reset_email_is_sent_to_known_email_address() {
        Mail::fake();

        // Attempt to request password reset
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ])->assertSuccessfulAPIResponse();

        Mail::assertSent(ResetPassword::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
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
    function password_request_can_be_requested_with_unknown_email_address() {
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => 'unknown@example.com'
        ])->assertSuccessfulAPIResponse();
    }

    /**
     * Test if the password reset email is not sent to an unknown email address.
     *
     * @return void
     * @test
     */
    function password_reset_email_is_not_sent_to_unknown_email_address() {
        Mail::fake();

        // Attempt to request password reset
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => 'unknown@example.com'
        ])->assertSuccessfulAPIResponse();

        Mail::assertNotSent(ResetPassword::class, function ($mail) {
            return $mail->hasTo($this->user->email);
        });
    }

    /**
     * Test if the password reset can only be requested once per 24 hours.
     *
     * @return void
     * @throws \Exception
     * @test
     */
    function password_reset_can_only_be_requested_once_per_24_hours() {
        // Create a password reset from 23 hours ago
        /** @var PasswordReset $oldPasswordReset */
        $oldPasswordReset = PasswordReset::create([
            'user_id'       => $this->user->id,
            'ip'            => 'FACTORY IS NEEDED HERE',
            'token'         => PasswordReset::genToken(),
            'created_at'    => now()->subHours(23)
        ]);

        // Attempt to request a new password reset
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ])->assertSuccessfulAPIResponse();

        // Check that there is still just one password reset
        $this->assertEquals(PasswordReset::where('user_id', $this->user->id)->count(), 1);

        // Delete the previous password reset
        $oldPasswordReset->delete();

        // Create a password reset from 25 hours ago
        /** @var PasswordReset $oldPasswordReset */
        PasswordReset::create([
            'user_id'       => $this->user->id,
            'ip'            => 'FACTORY IS NEEDED HERE',
            'token'         => PasswordReset::genToken(),
            'created_at'    => now()->subHours(25)
        ]);

        // Attempt to request a new password reset
        $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ])->assertSuccessfulAPIResponse();

        // Check that there are now two password resets
        $this->assertEquals(PasswordReset::where('user_id', $this->user->id)->count(), 2);
    }
}
