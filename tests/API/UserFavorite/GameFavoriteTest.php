<?php

namespace Tests\API\UserFavorite;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestGame;
use Tests\Traits\ProvidesTestUser;

class GameFavoriteTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestGame;

    /**
     * User can get a list of their favorite game.
     *
     * @return void
     */
    #[Test]
    function user_can_get_a_list_of_their_favorite_game(): void
    {
        // Add some game to the user's favorites
        $this->user->favorite($this->game);

        // Send request for the list of game
        $response = $this->auth()->getJson(route('api.users.favorites', [
            'user' => $this->user->getKey(),
            'library' => UserLibraryKind::Game,
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of game
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get their own favorite game.
     *
     * @return void
     */
    #[Test]
    function user_can_get_their_own_favorite_game(): void
    {
        // Add some game to the user's favorites
        $this->user->favorite($this->game);

        // Send request for the list of game
        $response = $this->auth()->getJson(route('api.me.favorites.index', [
            'library' => UserLibraryKind::Game,
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of game
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can add a game to their favorites.
     *
     * @return void
     */
    #[Test]
    function user_can_add_a_game_to_their_favorites(): void
    {
        // Send request for the list of game
        $response = $this->auth()->postJson(route('api.me.favorites.create', [
            'library' => UserLibraryKind::Game,
            'model_id' => $this->game->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of game
        $this->assertTrue($response->json()['data']['isFavorited']);
    }

    /**
     * User can remove a game from their favorites.
     *
     * @return void
     */
    #[Test]
    function user_can_remove_a_game_from_their_favorites(): void
    {
        // Add the game to the user's favorites.
        $this->user->favorite($this->game);

        // Send request for the list of game
        $response = $this->auth()->postJson(route('api.me.favorites.create', [
            'library' => UserLibraryKind::Game,
            'model_id' => $this->game->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of game
        $this->assertFalse($response->json()['data']['isFavorited']);
    }
}
