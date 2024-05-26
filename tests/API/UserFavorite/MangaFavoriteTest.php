<?php

namespace Tests\API\UserFavorite;

use App\Enums\UserLibraryKind;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestManga;
use Tests\Traits\ProvidesTestUser;

class MangaFavoriteTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestManga;

    /**
     * User can get a list of their favorite manga.
     *
     * @return void
     */
    #[Test]
    function user_can_get_a_list_of_their_favorite_manga(): void
    {
        // Add some manga to the user's favorites
        $this->user->favorite($this->manga);

        // Send request for the list of manga
        $response = $this->auth()->getJson(route('api.users.favorites', [
            'user' => $this->user->getKey(),
            'library' => UserLibraryKind::Manga,
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of manga
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get their own favorite manga.
     *
     * @return void
     */
    #[Test]
    function user_can_get_their_own_favorite_manga(): void
    {
        // Add some manga to the user's favorites
        $this->user->favorite($this->manga);

        // Send request for the list of manga
        $response = $this->auth()->getJson(route('api.me.favorites.index', [
            'library' => UserLibraryKind::Manga,
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of manga
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can add a manga to their favorites.
     *
     * @return void
     */
    #[Test]
    function user_can_add_a_manga_to_their_favorites(): void
    {
        // Send request for the list of manga
        $response = $this->auth()->postJson(route('api.me.favorites.create', [
            'library' => UserLibraryKind::Manga,
            'model_id' => $this->manga->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of manga
        $this->assertTrue($response->json()['data']['isFavorited']);
    }

    /**
     * User can remove a manga from their favorites.
     *
     * @return void
     */
    #[Test]
    function user_can_remove_a_manga_from_their_favorites(): void
    {
        // Add the manga to the user's favorites.
        $this->user->favorite($this->manga);

        // Send request for the list of manga
        $response = $this->auth()->postJson(route('api.me.favorites.create', [
            'library' => UserLibraryKind::Manga,
            'model_id' => $this->manga->getKey(),
        ]));

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the response contains the correct amount of manga
        $this->assertFalse($response->json()['data']['isFavorited']);
    }
}
