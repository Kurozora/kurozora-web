<?php

namespace Tests\API;

use App\Http\Resources\SessionResourceBasic;
use App\Session;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class SessionTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can get a list of their sessions.
     *
     * @return void
     * @test
     */
    function a_user_can_get_a_list_of_their_sessions()
    {
        // Create some sessions for the user
        factory(Session::class, 20)->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/sessions');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the current sessions
        $response->assertJson(['data' => ['current_session' => []]]);

        // Check whether the response contains the sessions
        $this->assertCount(20, $response->json()['data']['other_sessions']);
    }

    /**
     * Test if a user cannot get another user's list of sessions.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_another_users_list_of_sessions()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $anotherUser->id . '/sessions');

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can get the details of their session.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_details_of_their_session()
    {
        // Create a session for the user
        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/sessions/' . $session->id);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the sessions
        $this->assertTrue($response['data'] > 0);
    }

    /**
     * Test if a user cannot get the details of another user's session.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_the_details_of_another_users_session()
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $anotherUser->id]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/sessions/' . $session->id);

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if an expired session is deleted when validated by the user.
     *
     * @return void
     * @test
     */
    function an_expired_session_is_deleted_when_validated_by_the_user()
    {
        // Create a session for the user that expired a minute ago
        /** @var Session $session */
        $session = factory(Session::class)->create([
            'user_id'           => $this->user->id,
            'expires_at'        => now()->subMinute()
        ]);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/sessions/' . $session->id . '/validate');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the session was deleted
        $this->assertNull(Session::find($session->id));
    }

    /**
     * Test if a user cannot validate another user's session.
     *
     * @return void
     * @test
     */
    function a_user_cannot_validate_another_users_session()
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $anotherUser->id]);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/sessions/' . $session->id . '/validate');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can delete their session.
     *
     * @return void
     * @test
     */
    function a_user_can_delete_their_session()
    {
        // Create a session for the user
        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/sessions/' . $session->id . '/delete');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the session was deleted
        $this->assertNull(Session::find($session->id));
    }

    /**
     * Test if a user cannot delete another user's session.
     *
     * @return void
     * @test
     */
    function a_user_cannot_delete_another_users_session()
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $anotherUser->id]);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/sessions/' . $session->id . '/delete');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the session still exists
        $this->assertNotNull(Session::find($session->id));
    }

    /** @test */
    function a_user_can_update_the_apn_device_token_of_their_session()
    {
        // Create a session for the user
        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $this->user->id]);

        // Create a new token
        $newToken = Str::random(64);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/sessions/' . $session->id . '/update', [
            'apn_device_token' => $newToken
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the token was updated
        $session->refresh();
        $this->assertSame($newToken, $session->apn_device_token);
    }

    /** @test */
    function a_user_cannot_update_the_apn_device_token_of_another_users_session()
    {
        // Create a session for the user
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        /** @var Session $session */
        $session = factory(Session::class)->create(['user_id' => $anotherUser->id]);

        // Create a new token
        $newToken = Str::random(64);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/sessions/' . $session->id . '/update', [
            'apn_device_token' => $newToken
        ]);

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }
}
