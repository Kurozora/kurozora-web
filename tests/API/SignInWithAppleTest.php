<?php

namespace Tests\API;

use App\Helpers\Settings;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class SignInWithAppleTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * An account can be signed up.
     *
     * @return void
     * @test
     */
    function an_account_can_be_signed_up_via_siwa()
    {
        $this->json('POST', '/api/v1/users/signin/siwa', [
            'token'             => 'eyJraWQiOiJlWGF1bm1MIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiYXBwLmt1cm96b3JhLnRyYWNrZXIiLCJleHAiOjE1ODI3MjczNTcsImlhdCI6MTU4MjcyNjc1Nywic3ViIjoiMDAxMTUxLjZhNTRmM2JhZmE1MDQxYmJhMDY5M2ZkMTA3OWIwZTc4LjEzNTQiLCJjX2hhc2giOiIxS3lzLURLbDAzUmNJZUFvYmlaUlFnIiwiZW1haWwiOiJtdTR5NzZtN2Q3QHByaXZhdGVyZWxheS5hcHBsZWlkLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSIsImlzX3ByaXZhdGVfZW1haWwiOiJ0cnVlIiwiYXV0aF90aW1lIjoxNTgyNzI2NzU3LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.qFHuG25KsAYybjJ9DXl9X2bq64eejYQ5bfdG0xH5xySu38BFNxGi15t18209JdwiuAO3PPSt8l-DdFJU6WB568-UedbiASo6TBVKAdqjX4bG7VJdUqS5NgMJRARhLQ19EF6DHGDQF4JNkzzkcPxkq2YViSdtnMfceXl-irh3rsK3ISxz2eMshcvpOAVnJPWGwwiAJQq33FlIISbVn2TKbDVsunAgSNpNFzZV7uQEFxxmEqMPqA393XkWKOWeJpP4b-N1aMTfiqBMjZLO4laoClGur1bIaJUmPqo-hNSTu7ubNQhVhkBVpdlUo83KJDr6d6wxl6ZgMF70nmchx2H7Tw',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();

        // Check whether the account was created
        $this->assertEquals(1, User::count(), 'The user account was not created.');

        // Check whether a session was created
        $this->assertEquals(1, Session::count(), 'A session was not created.');

        // Check whether the user can change their username
        $settings = Settings::create(User::first());
        $this->assertTrue($settings->get('can_change_username'), 'The user cannot change their username when it should be possible.');
    }

    /**
     * Test if a user can sign in via SIWA.
     *
     * @return void
     * @test
     */
    function a_user_can_sign_in_via_SIWA()
    {
        // Create a SIWA user
        User::factory()->create([
            'email'     => 'mu4y76m7d7@privaterelay.appleid.com',
            'siwa_id'   => '001151.6a54f3bafa5041bba0693fd1079b0e78.1354'
        ]);

        // Make the login request
        $response = $this->json('POST', '/api/v1/users/signin/siwa', [
            'token'    => 'eyJraWQiOiJlWGF1bm1MIiwiYWxnIjoiUlMyNTYifQ.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiYXBwLmt1cm96b3JhLnRyYWNrZXIiLCJleHAiOjE1ODI3MjczNTcsImlhdCI6MTU4MjcyNjc1Nywic3ViIjoiMDAxMTUxLjZhNTRmM2JhZmE1MDQxYmJhMDY5M2ZkMTA3OWIwZTc4LjEzNTQiLCJjX2hhc2giOiIxS3lzLURLbDAzUmNJZUFvYmlaUlFnIiwiZW1haWwiOiJtdTR5NzZtN2Q3QHByaXZhdGVyZWxheS5hcHBsZWlkLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSIsImlzX3ByaXZhdGVfZW1haWwiOiJ0cnVlIiwiYXV0aF90aW1lIjoxNTgyNzI2NzU3LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.qFHuG25KsAYybjJ9DXl9X2bq64eejYQ5bfdG0xH5xySu38BFNxGi15t18209JdwiuAO3PPSt8l-DdFJU6WB568-UedbiASo6TBVKAdqjX4bG7VJdUqS5NgMJRARhLQ19EF6DHGDQF4JNkzzkcPxkq2YViSdtnMfceXl-irh3rsK3ISxz2eMshcvpOAVnJPWGwwiAJQq33FlIISbVn2TKbDVsunAgSNpNFzZV7uQEFxxmEqMPqA393XkWKOWeJpP4b-N1aMTfiqBMjZLO4laoClGur1bIaJUmPqo-hNSTu7ubNQhVhkBVpdlUo83KJDr6d6wxl6ZgMF70nmchx2H7Tw',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ]);

        $response->assertSuccessfulAPIResponse();

        // Check whether a session was created
        $this->assertEquals(1, Session::count(), 'A session was not created.');
    }
}
