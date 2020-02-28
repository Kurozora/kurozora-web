<?php

namespace Tests\API;

use App\Session;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SIWAAccountRegistrationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Test if an account can be registered.
     *
     * @return void
     * @test
     */
    function an_account_can_be_registered_via_siwa()
    {
        $this->json('POST', '/api/v1/users/register-siwa', [
            'email'             => 'jdi8eji2@privaterelay.appleid.com',
            'siwa_id'           => '001100.6a54b3ccop5041bba0773fd1079b0e22.2447',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();

        // Check whether the account was created
        $this->assertEquals(User::count(), 1, 'The user account was not created.');

        // Check whether a session was created
        $this->assertEquals(Session::count(), 1, 'A session was not created.');

        // Check whether the user can change their username
        /** @var User $user */
        $user = User::first();
        $this->assertTrue($user->username_change_available, 'The user does not have a username change available.');
    }
}
