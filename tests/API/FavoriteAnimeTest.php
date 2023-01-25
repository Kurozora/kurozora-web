<?php

namespace Tests\API;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class FavoriteAnimeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * User can add anime to their favorites.
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_favorites(): void
    {
        // Send request to add anime to the user's favorites
        /** @var Anime $anime */
        $anime = Anime::factory()->create();

        $this->user->track($anime, UserLibraryStatus::InProgress());

        $response = $this->auth()->json('POST', 'v1/me/favorite-anime', [
            'anime_id'      => $anime->id,
            'is_favorite'   => 1
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user has favorited the anime
        $this->assertTrue($this->user->hasFavorited($anime));
    }

    /**
     * User can remove anime from their favorites.
     *
     * @return void
     * @test
     */
    function user_can_remove_anime_from_their_favorites(): void
    {
        // Add the anime to the user's favorites
        /** @var Anime $anime */
        $anime = Anime::factory()->create();

        $this->user->track($anime, UserLibraryStatus::InProgress());
        $this->user->favorite($anime);

        // Send request to remove the anime from the user's favorites
        $response = $this->auth()->json('POST', 'v1/me/favorite-anime', [
            'anime_id'      => $anime->id,
            'is_favorite'   => 0
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the user now not favorited the anime
        $this->assertFalse($this->user->hasFavorited($anime));
    }

//    /**
//     * == This is currently disabled, because there is no preference system in place
//     * == to facilitate this behavior
//     *
//     * A user cannot get a list of another user's anime favorites.
//     *
//     * @return void
//     * @test
//     */
//    function a_user_cannot_get_a_list_of_another_users_anime_favorites()
//    {
//        $this->markTestIncomplete('\\
//        This is currently disabled, because there is\\
//        no preference system in place to facilitate this behavior
//        ');
//
//        // Send request to get the other user's list of anime favorites
//        /** @var User $anotherUser */
//        //$anotherUser = User::factory()->create();
//
//        //$response = $this->auth()->json('GET', 'v1/users/' . $anotherUser->id . '/favorite-anime');
//
//        // Check whether the request was unsuccessful
//        //$response->assertUnsuccessfulAPIResponse();
//    }
}
