<?php

namespace Tests\API;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SignInWithAppleTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * An account can be signed up.
     *
     * @return void
     */
    #[Test]
    function an_account_can_be_signed_up_via_siwa(): void
    {
        $this->json('POST', 'v1/users/siwa/signin', [
            'token'             => 'eyJraWQiOiJZdXlYb1kiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiYXBwLmt1cm96b3JhLnRyYWNrZXIiLCJleHAiOjE2NDY1MjMyMTksImlhdCI6MTY0NjQzNjgxOSwic3ViIjoiMDAxMTUxLjZhNTRmM2JhZmE1MDQxYmJhMDY5M2ZkMTA3OWIwZTc4LjEzNTQiLCJjX2hhc2giOiJwMlJEcUdXNmlsSmFxVnhtOVZpVHVBIiwiZW1haWwiOiJtdTR5NzZtN2Q3QHByaXZhdGVyZWxheS5hcHBsZWlkLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSIsImlzX3ByaXZhdGVfZW1haWwiOiJ0cnVlIiwiYXV0aF90aW1lIjoxNjQ2NDM2ODE5LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.GbEOfQF7bYqcjliA0ppyjWCfwLgJ-S2c6C-Cb9r9TLYME_xMgwzvb3wNwcmoqGTJCnP36ScUP0rVhyLpxKzCCPUMGAkG7EUzrQUZ00y-g8YHRnLkJspMklYA3TnSMVe01DD4_hzEwY4JC-buNay24RX8JsJJa10EO2ZP7Olf-PGv0ORXLiU8zYeWpZ7dy_hq7aScoC6s3iaTNqpuqe2mGuWYEyKMnBIHNZqqejgHaGHf6xpWfIJVP7waLUAeU4g7wfzGxXFQLHc3RqdpsqT6aE7ueN5opotudqpZsMDX9QG0AZYhK4Jwr4c20GNwXJj3YjZPxwgLvxwqI_Gj7z4Ivw',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ])->assertSuccessfulAPIResponse();

        // Check whether the account was created
        $this->assertEquals(1, User::count(), 'The user account was not created.');

        // Check whether a personal access token was created
        $this->assertEquals(1, PersonalAccessToken::count(), 'A personal access token was not created.');

        // Check whether the user can change their username
        $user = User::first();
        $this->assertTrue($user->can_change_username, 'The user cannot change their username when it should be possible.');
    }

    /**
     * Test if a user can sign in via SIWA.
     *
     * @return void
     */
    #[Test]
    function a_user_can_sign_in_via_SIWA(): void
    {
        // Create a SIWA user
        User::factory()->create([
            'email'     => 'mu4y76m7d7@privaterelay.appleid.com',
            'siwa_id'   => '001151.6a54f3bafa5041bba0693fd1079b0e78.1354'
        ]);

        // Make the login request
        $response = $this->json('POST', 'v1/users/siwa/signin', [
            'token'             => 'eyJraWQiOiJZdXlYb1kiLCJhbGciOiJSUzI1NiJ9.eyJpc3MiOiJodHRwczovL2FwcGxlaWQuYXBwbGUuY29tIiwiYXVkIjoiYXBwLmt1cm96b3JhLnRyYWNrZXIiLCJleHAiOjE2NDY1MjMyMTksImlhdCI6MTY0NjQzNjgxOSwic3ViIjoiMDAxMTUxLjZhNTRmM2JhZmE1MDQxYmJhMDY5M2ZkMTA3OWIwZTc4LjEzNTQiLCJjX2hhc2giOiJwMlJEcUdXNmlsSmFxVnhtOVZpVHVBIiwiZW1haWwiOiJtdTR5NzZtN2Q3QHByaXZhdGVyZWxheS5hcHBsZWlkLmNvbSIsImVtYWlsX3ZlcmlmaWVkIjoidHJ1ZSIsImlzX3ByaXZhdGVfZW1haWwiOiJ0cnVlIiwiYXV0aF90aW1lIjoxNjQ2NDM2ODE5LCJub25jZV9zdXBwb3J0ZWQiOnRydWV9.GbEOfQF7bYqcjliA0ppyjWCfwLgJ-S2c6C-Cb9r9TLYME_xMgwzvb3wNwcmoqGTJCnP36ScUP0rVhyLpxKzCCPUMGAkG7EUzrQUZ00y-g8YHRnLkJspMklYA3TnSMVe01DD4_hzEwY4JC-buNay24RX8JsJJa10EO2ZP7Olf-PGv0ORXLiU8zYeWpZ7dy_hq7aScoC6s3iaTNqpuqe2mGuWYEyKMnBIHNZqqejgHaGHf6xpWfIJVP7waLUAeU4g7wfzGxXFQLHc3RqdpsqT6aE7ueN5opotudqpZsMDX9QG0AZYhK4Jwr4c20GNwXJj3YjZPxwgLvxwqI_Gj7z4Ivw',
            'platform'          => 'iOS',
            'platform_version'  => '13.4',
            'device_vendor'     => 'Apple',
            'device_model'      => 'iPhone 11 Pro Max'
        ]);

        $response->assertSuccessfulAPIResponse();

        // Check whether a personal access token was created
        $this->assertEquals(1, PersonalAccessToken::count(), 'A personal access token was not created.');
    }
}
