<?php

namespace Tests\API;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\API\Traits\ProvidesTestUser;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can login.
     *
     * @return void
     * @test
     */
    function a_user_can_login()
    {
        $this->json('POST', '/api/v1/sessions', [
            'email'             => $this->user->email,
            'password'          => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();

        // Check whether a session was created for the user
        $this->assertEquals($this->user->sessions()->count(), 1);
    }

    /**
     * Test if a user cannot login with an incorrect password.
     *
     * @return void
     * @test
     */
    function a_user_cannot_login_with_an_incorrect_password()
    {
        $this->json('POST', '/api/v1/sessions', [
            'email'     => $this->user->email,
            'password'  => $this->userPassword . 'invalid',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertUnsuccessfulAPIResponse();

        // Check that no session was created
        $this->assertEquals($this->user->sessions()->count(), 0);
    }

    /**
     * Test if a user cannot login with an unknown email.
     *
     * @return void
     * @test
     */
    function a_user_cannot_login_with_an_unknown_email()
    {
        $this->json('POST', '/api/v1/sessions', [
            'email'     => 'invalidemail@example.com',
            'password'  => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can only attempt to login 3 times per 5 minutes.
     *
     * @return void
     * @test
     */
    function a_user_can_only_attempt_to_login_3_times_per_5_minutes()
    {
        // Make 3 login attempts with wrong password
        for($i = 0; $i < 3; $i++)
            $this->json('POST', '/api/v1/sessions', [
                'email'     => $this->user->email,
                'password'  => $this->userPassword . 'invalid',
                'platform'          => 'iOS',
                'platform_version'  => '13.4',
                'device_vendor'     => 'Apple',
                'device_model'      => 'iPhone 11 Pro Max'
            ])->assertUnsuccessfulAPIResponse();

        // 4th attempt with correct password should fail
        $this->json('POST', '/api/v1/sessions', [
            'email'     => $this->user->email,
            'password'  => $this->userPassword,
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertUnsuccessfulAPIResponse();

        // Time travel to the future
        Carbon::setTestNow(now()->addMinutes(6));

        // Should now be able to login, because cooldown is over
        $this->json('POST', '/api/v1/sessions', [
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
     * @test
     */
    function a_user_receives_a_notification_when_someone_logs_into_their_account()
    {
        $this->json('POST', '/api/v1/sessions', [
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
