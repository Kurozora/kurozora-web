<?php

namespace Tests\API\UserFavorite;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class AnimeFavoriteTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * User can get a list of their favorite anime.
     *
     * @return void
     */
    #[Test]
    function user_can_get_a_list_of_their_favorite_anime(): void
    {
        // Add some anime to the user's favorites
        $this->user->favorite($this->anime);

        // Send request for the list of anime
        $response = $this->auth()->getJson(route('api.users.favorites', [
            'user' => $this->user->getKey(),
            'library' => UserLibraryKind::Anime,
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get their own favorite anime.
     *
     * @return void
     */
    #[Test]
    function user_can_get_their_own_favorite_anime(): void
    {
        // Add some anime to the user's favorites
        $this->user->favorite($this->anime);

        // Send request for the list of anime
        $response = $this->auth()->getJson(route('api.me.favorites.index', [
            'library' => UserLibraryKind::Anime,
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can add an anime to their favorites.
     *
     * @return void
     */
    #[Test]
    function user_can_add_an_anime_to_their_favorites(): void
    {
        // Send request for the list of anime
        $response = $this->auth()->postJson(route('api.me.favorites.create', [
            'library' => UserLibraryKind::Anime,
            'model_id' => $this->anime->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertTrue($response->json()['data']['isFavorited']);
    }

    /**
     * User can remove an anime from their favorites.
     *
     * @return void
     */
    #[Test]
    function user_can_remove_an_anime_from_their_favorites(): void
    {
        // Add the anime to the user's favorites.
        $this->user->favorite($this->anime);

        // Send request for the list of anime
        $response = $this->auth()->postJson(route('api.me.favorites.create', [
            'library' => UserLibraryKind::Anime,
            'model_id' => $this->anime->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of anime
        $this->assertFalse($response->json()['data']['isFavorited']);
    }
}
