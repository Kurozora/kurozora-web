<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestGame;
use Tests\Traits\ProvidesTestUser;

class GameTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestGame;

    /**
     * A user can view the cast of an anime.
     *
     * @return void
     * @test
     */
     public function a_user_can_view_the_cast_of_a_game(): void
     {
         $response = $this->getJson(route('api.games.cast', $this->game->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the cast array is not empty
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view the related anime of an anime.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_the_related_games_of_a_game(): void
    {
        $response = $this->getJson(route('api.games.related-games', $this->game->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the related array is not empty
        $this->assertNotEmpty($response->json()['data'][0]);
    }

    /**
     * An authenticated user can view the related anime of an anime with personal information.
     *
     * @return void
     * @test
     */
    public function an_authenticated_user_can_view_the_related_games_of_an_games_with_personal_information(): void
    {
        $response = $this->auth()->getJson(route('api.games.related-games', $this->game->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the current_user array is not empty
        $this->assertArrayHasKey('libraryStatus', $response->json()['data'][0]['game']['attributes']);
    }
}
