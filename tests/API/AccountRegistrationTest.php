<?php

namespace Tests\API;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class AccountRegistrationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test if an account can be registered.
     *
     * @return void
     * @test
     */
    function an_account_can_be_registered()
    {
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(User::count(), 1);
    }

    /**
 * Test if an account cannot be registered when the username is already in use.
 *
 * @return void
 * @test
 */
    function an_account_cannot_be_registered_with_a_username_that_is_already_in_use()
    {
        // Create the first account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(User::count(), 1);

        // Attempt to create the second account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'unique@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that there is just 1 account
        $this->assertEquals(User::count(), 1);
    }

    /**
     * Test if an account cannot be registered when the email is already in use.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_an_email_that_is_already_in_use()
    {
        // Create the first account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertSuccessfulAPIResponse();

        // Double check that the account was created
        $this->assertEquals(User::count(), 1);

        // Attempt to create the second account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'UniqueUsername',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that there is just 1 account
        $this->assertEquals(User::count(), 1);
    }

    /**
     * Test if an account cannot be registered with a long password.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_long_password()
    {
        // Generate a password with size of 256
        $longPassword = Str::random(256);

        // Attempt to register the account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'UniqueUsername',
            'password'  => $longPassword,
            'email'     => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(User::count(), 0);
    }

    /**
     * Test if an account cannot be registered with a short password.
     *
     * @return void
     * @test
     */
    function an_account_cannot_be_registered_with_a_short_password()
    {
        // Attempt to register the account
        $this->json('POST', '/api/v1/users', [
            'username'  => 'UniqueUsername',
            'password'  => 'hi',
            'email'     => 'tester@kurozora.app'
        ])->assertUnsuccessfulAPIResponse();

        // Double check that the account was not created
        $this->assertEquals(User::count(), 0);
    }
}
