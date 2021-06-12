<?php

namespace Tests\API;

use App\Models\Anime;
use App\Enums\UserLibraryStatus;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class LibraryTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * User can get the Watching anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_Watching_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'Watching'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the Dropped anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_Dropped_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::Dropped]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'Dropped'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the Planning anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_Planning_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::Planning]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'Planning'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the Completed anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_Completed_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::Completed]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'Completed'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['data']);
    }

    /**
     * User can get the OnHold anime in their library.
     *
     * @return void
     * @test
     */
    function user_can_get_the_OnHold_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::OnHold]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'OnHold'
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
    function user_cannot_get_the_anime_in_their_library_with_an_invalid_status()
    {
        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library', [
            'status' => 'Invalid Status'
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can add anime to their library (Watching).
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_library_with_status_Watching()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, 'Watching');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Watching list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Watching)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their library (Dropped).
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_library_with_status_Dropped()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, 'Dropped');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Dropped list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Dropped)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their library (Planning).
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_library_with_status_Planning()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, 'Planning');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Planning list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Planning)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their library (Completed).
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_library_with_status_Completed()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, 'Completed');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Completed list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Completed)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * User can add anime to their library (OnHold).
     *
     * @return void
     * @test
     */
    function user_can_add_anime_to_their_library_with_status_OnHold()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, 'OnHold');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their OnHold list
        $count = $this->user->library()
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
    function user_cannot_add_anime_to_their_library_with_an_invalid_status()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anime->id, 'Invalid Status');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can delete anime from their library.
     *
     * @return void
     * @test
     */
    function user_can_delete_anime_from_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach($this->anime->id, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('POST', 'v1/me/library/delete', [
            'anime_id' => 1
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has no anime in their library
        $count = $this->user->library()->count();

        $this->assertEquals(0, $count);
    }

    /**
     * User can search in own library.
     *
     * @return void
     * @test
     */
    function user_can_search_in_own_library()
    {
        // Add an anime to the user's list
        $shows = Anime::factory(20)->create();

        /** @var Anime $show */
        foreach ($shows as $show)
            $this->user->library()->attach($show->id, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('GET', 'v1/me/library/search', [
            'query' => $shows->first()->original_title
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
     * @param string $status
     * @return TestResponse
     */
    private function addAnimeToLibraryAPIRequest(int $animeID, string $status): TestResponse
    {
        return $this->auth()->json('POST', 'v1/me/library', [
            'anime_id'  => $animeID,
            'status'    => $status
        ]);
    }
}
