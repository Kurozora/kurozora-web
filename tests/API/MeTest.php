<?php

namespace Tests\API;

use App\Anime;
use App\Session;
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
        $response = $this->auth()->json('GET', '/api/v1/me');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user id in the response is the current user's id
        $this->assertEquals($this->user->id, $response->json()['data'][0]['id']);
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
        $response = $this->json('GET', '/api/v1/me', []);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can get a list of their favorite anime.
     *
     * @return void
     * @test
     */
    function user_can_get_a_list_of_their_favorite_anime()
    {
        // Add some anime to the user's favorites
        /** @var Anime[] $anime */
        $animeList = factory(Anime::class, 25)->create();

        foreach($animeList as $anime)
            $this->user->favoriteAnime()->attach($anime->id);

        // Send request for the list of anime
        $response = $this->auth()->json('GET', '/api/v1/me/favorite-anime');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(25, $response->json()['data']);
    }

    /**
     * User can get a list of their sessions.
     *
     * @return void
     * @test
     */
    function user_can_get_a_list_of_their_sessions()
    {
        // Create some sessions for the user
        factory(Session::class, 25)->create(['user_id' => $this->user->id]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/me/sessions');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the sessions
        $this->assertCount(25, $response->json()['data']);
    }

}
