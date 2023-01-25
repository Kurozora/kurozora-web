<?php

namespace Tests\API;

use App\Enums\SearchScope;
use App\Enums\SearchType;
use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class LibraryTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * User can get the watching anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_watching_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => UserLibraryStatus::InProgress()->key
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the dropped anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_dropped_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::Dropped());

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => UserLibraryStatus::Dropped()->key
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the planning anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_planning_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::Planning());

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => UserLibraryStatus::Planning()->key
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the completed anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_completed_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::Completed());

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => UserLibraryStatus::Completed()->key
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the on-hold anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_on_hold_anime_in_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::OnHold());

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => UserLibraryStatus::OnHold()->key
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User cannot get the anime in their library with an invalid status.
     *
     * @return void
     * @test
     */
    function user_cannot_get_the_anime_in_their_library_with_an_invalid_status(): void
    {
        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'Invalid Status'
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can add anime to their watching library.
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_watching_library(): void
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, UserLibraryStatus::InProgress());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Watching list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::InProgress)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their dropped library.
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_dropped_library(): void
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, UserLibraryStatus::Dropped());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Dropped list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::Dropped)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their planning library.
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_planning_library(): void
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, UserLibraryStatus::Planning());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Planning list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::Planning)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their completed library.
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_completed_library(): void
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, UserLibraryStatus::Completed());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Completed list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::Completed)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their on-hold library.
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_on_hold_library(): void
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, UserLibraryStatus::OnHold());

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their OnHold list
        $count = $this->user->whereTracked(Anime::class)
            ->wherePivot('status', UserLibraryStatus::OnHold)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User cannot add anime to their library with an invalid status.
     *
     * @return void
     * @test
     */
    function user_cannot_add_anime_to_their_library_with_an_invalid_status(): void
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, null);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can delete anime from their library.
     *
     * @return void
     * @test
     */
    function user_can_delete_anime_from_their_library(): void
    {
        // Add an anime to the list
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Send the request
        $response = $this->auth()->json('POST', 'v1/me/library/delete', [
            'anime_id' => 1
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user has no longer tracked the anime
        $this->assertTrue($this->user->hasNotTracked($this->anime));
    }

    /**
     * User can search in own library.
     *
     * @return void
     * @test
     */
    function user_can_search_in_own_library(): void
    {
        // Add an anime to the user's list
        $animes = Anime::factory(20)->create();

        /** @var Anime $anime */
        foreach ($animes as $anime) {
            $this->user->track($anime, UserLibraryStatus::InProgress());
        }

        // Send the request
        $response = $this->auth()->json('GET', 'v1/search', [
            'scope' => SearchScope::Library,
            'types' => [SearchType::Shows],
            'query' => $animes->first()->original_title,
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the result count is greater than zero
        $this->assertGreaterThan(0, count($response->json('data')));
    }

    /**
     * Sends an API request to add anime to a user's library.
     *
     * @param int $animeID
     * @param UserLibraryStatus|null $status
     * @return TestResponse
     */
    private function addAnimeToLibraryAPIRequest(int $animeID, ?UserLibraryStatus $status): TestResponse
    {
        return $this->auth()->json('POST', 'v1/me/library', [
            'anime_id'  => $animeID,
            'status'    => $status?->key ?? 'Invalid status'
        ]);
    }
}
