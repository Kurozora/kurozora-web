<?php

namespace Tests\API;

use App\Mail\ResetPassword;
use App\Models\PasswordReset;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class ResetPasswordTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a password reset cannot be requested with an invalid email format.
     *
     * @return void
     * @test
     */
    function password_reset_cannot_be_requested_with_an_invalid_email_address_format()
    {
        $response = $this->json('POST', '/api/v1/users/reset-password', [
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
    function password_request_can_be_requested_with_known_email_address()
    {
        $response = $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ]);
        $response->assertSuccessfulAPIResponse();
    }

    /**
     * Test if the password reset email is sent to a known email address.
     *
     * @return void
     * @test
     */
    function password_reset_email_is_sent_to_known_email_address()
    {
        Mail::fake();

        // Attempt to request password reset
        $response = $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ]);
        $response->assertSuccessfulAPIResponse();

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
    function password_request_can_be_requested_with_unknown_email_address()
    {
        $response = $this->json('POST', '/api/v1/users/reset-password', [
            'email' => 'unknown@example.com'
        ]);
        $response->assertSuccessfulAPIResponse();
    }

    /**
     * Test if the password reset email is not sent to an unknown email address.
     *
     * @return void
     * @test
     */
    function password_reset_email_is_not_sent_to_unknown_email_address()
    {
        Mail::fake();

        // Attempt to request password reset
        $response = $this->json('POST', '/api/v1/users/reset-password', [
            'email' => 'unknown@example.com'
        ]);
        $response->assertSuccessfulAPIResponse();

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
    function password_reset_can_only_be_requested_once_per_24_hours()
    {
        // Create a password reset from 23 hours ago
        /** @var PasswordReset $oldPasswordReset */
        $oldPasswordReset = PasswordReset::factory()->create([
            'user_id'       => $this->user->id,
            'created_at'    => now()->subHours(23)
        ]);

        // Attempt to request a new password reset
        $request = $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ]);

        $request->assertSuccessfulAPIResponse();

        // Check that there is still just one password reset
        $this->assertEquals(1, PasswordReset::where('user_id', $this->user->id)->count());

        // Delete the previous password reset
        $oldPasswordReset->delete();

        // Create a password reset from 25 hours ago
        PasswordReset::factory()->create([
            'user_id'       => $this->user->id,
            'created_at'    => now()->subHours(25)
        ]);

        // Attempt to request a new password reset
        $request = $this->json('POST', '/api/v1/users/reset-password', [
            'email' => $this->user->email
        ]);

        $request->assertSuccessfulAPIResponse();

        // Check that there are now two password resets
        $this->assertEquals(2, PasswordReset::where('user_id', $this->user->id)->count());
    }

    /**
     * Test if the password reset email contains a link to reset.
     *
     * @return void
     * @test
     * @throws \ReflectionException
     */
    function password_reset_email_contains_link()
    {
        $passwordReset = PasswordReset::factory()->create([
            'user_id'       => $this->user->id,
            'created_at'    => now()->subHours(25)
        ]);

        // Create the email
        $email = new ResetPassword($this->user, $passwordReset);

        // Check that the link is in the email
        $this->assertStringContainsString(
            route('password.reset', ['token' => $passwordReset->token]),
            $email->render()
        );
    }
}
