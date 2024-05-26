<?php

namespace Tests\API;

use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class SessionTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * User can get the details of their session.
     *
     * @return void
     */
    #[Test]
    function user_can_get_the_details_of_their_session(): void
    {
        // Create a session for the user
        /** @var Session $session */
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/sessions/' . $session->id);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the sessions
        $this->assertNotEmpty($response['data']);
    }

    /**
     * User cannot get the details of another user's session.
     *
     * @return void
     */
    #[Test]
    function user_cannot_get_the_details_of_another_users_session(): void
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        /** @var Session $session */
        $session = Session::factory()->create(['user_id' => $anotherUser->id]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/sessions/' . $session->id);

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can delete their session.
     *
     * @return void
     */
    #[Test]
    function user_can_delete_their_session(): void
    {
        // Create a session for the user
        /** @var Session $session */
        $session = Session::factory()->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('POST', 'v1/me/sessions/' . $session->id . '/delete');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the session was deleted
        $this->assertNull(Session::find($session->id));
    }

    /**
     * User cannot delete another user's session.
     *
     * @return void
     */
    #[Test]
    function user_cannot_delete_another_users_session(): void
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        /** @var Session $session */
        $session = Session::factory()->create(['user_id' => $anotherUser->id]);

        // Send the request
        $response = $this->auth()->json('POST', 'v1/me/sessions/' . $session->id . '/delete');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the session still exists
        $this->assertNotNull(Session::find($session->id));
    }

    /**
     * User cannot update the apn device token of another users' session.
     *
     * @return void
     */
    #[Test]
    function user_cannot_update_the_apn_device_token_of_another_users_session(): void
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        /** @var Session $session */
        $session = Session::factory()->create(['user_id' => $anotherUser->id]);

        // Create a new token
        $newToken = Str::random(64);

        // Send the request
        $response = $this->auth()->json('POST', 'v1/me/sessions/' . $session->id . '/update', [
            'apn_device_token' => $newToken
        ]);

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }
}
