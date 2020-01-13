<?php

namespace Tests\API;

use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
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
    public function an_account_can_be_registered()
    {
        $response = $this->post('/api/v1/users', [
            'username'  => 'KurozoraTester',
            'password'  => 'StrongPassword909@!',
            'email'     => 'tester@kurozora.app'
        ]);

        // Assert successful response from API
        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);

        // Double check that the account was created
        $this->assertEquals(User::count(), 1);
    }
}
