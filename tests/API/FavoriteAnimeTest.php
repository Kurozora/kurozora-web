<?php

namespace Tests\API;

use App\Anime;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class FavoriteAnimeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can add anime to their favorites.
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_favorites()
    {
        // Send request to add anime to the user's favorites
        /** @var Anime $anime */
        $anime = factory(Anime::class)->create();

        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/favorite-anime', [
            'anime_id'      => $anime->id,
            'is_favorite'   => 1
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has 1 anime in their favorites
        $this->assertEquals(1, $this->user->favoriteAnime()->count());
    }

    /**
     * Test if a user can remove anime from their favorites.
     *
     * @return void
     * @test
     */
    function a_user_can_remove_anime_from_their_favorites()
    {
        // Add the anime to the user's favorites
        /** @var Anime $anime */
        $anime = factory(Anime::class)->create();

        $this->user->favoriteAnime()->attach($anime->id);

        // Send request to remove the anime from the user's favorites
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/favorite-anime', [
            'anime_id'      => $anime->id,
            'is_favorite'   => 0
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now has no anime in their favorites
        $this->assertEquals(0, $this->user->favoriteAnime()->count());
    }

    /**
     * Test if a user cannot add anime to another user's favorites.
     *
     * @return void
     * @test
     */
    function a_user_cannot_add_anime_to_another_users_favorites()
    {
        // Send request to add anime to the user's favorites
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        /** @var Anime $anime */
        $anime = factory(Anime::class)->create();

        $response = $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/favorite-anime', [
            'anime_id'      => $anime->id,
            'is_favorite'   => 1
        ]);

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the user still has no anime in their favorites
        $this->assertEquals(0, $anotherUser->favoriteAnime()->count());
    }

    /**
     * Test if a user can get a list of the anime in their favorites.
     *
     * @return void
     * @test
     */
    function a_user_can_get_a_list_of_the_anime_in_their_favorites()
    {
        // Add some anime to the user's favorites
        /** @var Anime[] $anime */
        $animeList = factory(Anime::class, 30)->create();

        foreach($animeList as $anime)
            $this->user->favoriteAnime()->attach($anime->id);

        // Send request for the list of anime
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/favorite-anime');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(30, $response->json()['anime']);
    }

    /**
     * == This is currently disabled, because there is no preference system in place
     * == to facilitate this behavior
     *
     * Test if a user cannot get a list of another user's anime favorites.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_a_list_of_another_users_anime_favorites()
    {
        $this->markTestIncomplete('\\
        This is currently disabled, because there is\\
        no preference system in place to facilitate this behavior
        ');

        // Send request to get the other user's list of anime favorites
        /** @var User $anotherUser */
        //$anotherUser = factory(User::class)->create();

        //$response = $this->auth()->json('GET', '/api/v1/users/' . $anotherUser->id . '/favorite-anime');

        // Check whether the request was unsuccessful
        //$response->assertUnsuccessfulAPIResponse();
    }
}
