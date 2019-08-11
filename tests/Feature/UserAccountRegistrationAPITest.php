<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserAccountRegistrationAPITest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_if_an_account_can_be_created()
    {
        $response = $this->json('POST', '/api/v1/users', [
            'username'  => 'Kurozora',
            'password'  => 'thisIsATestCase5!',
            'email'     => 'info@kurozora.app'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }
}
