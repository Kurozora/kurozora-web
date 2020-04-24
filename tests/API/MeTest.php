<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class MeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * User can get own details with authentication token.
     *
     * @return void
     * @test
     */
    public function user_can_get_own_details_with_authentication_token()
    {
        // Send request
        $response = $this->auth()->json('GET', '/api/v1/users/me', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user id in the response is the current user's id
        $this->assertEquals($response->json()['user']['id'], $this->user->id);
    }

    /**
     * User cannot get own details without authentication token.
     *
     * @return void
     * @test
     */
    public function user_cannot_get_own_details_without_authentication_token()
    {
        // Send request
        $response = $this->json('GET', '/api/v1/users/me', []);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }
}
