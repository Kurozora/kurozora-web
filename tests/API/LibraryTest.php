<?php

namespace Tests\API;

use App\Anime;
use App\Enums\UserLibraryStatus;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\ProvidesTestUser;
use Tests\Traits\RunsSeeders;
use Tests\TestCase;

class LibraryTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, RunsSeeders;

    /**
     * Test if a user can get the Watching anime in their library.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_Watching_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach(1, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library', [
            'status' => 'Watching'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['anime']);
    }

    /**
     * Test if a user can get the Dropped anime in their library.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_Dropped_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach(1, ['status' => UserLibraryStatus::Dropped]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library', [
            'status' => 'Dropped'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['anime']);
    }

    /**
     * Test if a user can get the Planning anime in their library.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_Planning_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach(1, ['status' => UserLibraryStatus::Planning]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library', [
            'status' => 'Planning'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['anime']);
    }

    /**
     * Test if a user can get the Completed anime in their library.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_Completed_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach(1, ['status' => UserLibraryStatus::Completed]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library', [
            'status' => 'Completed'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['anime']);
    }

    /**
     * Test if a user can get the OnHold anime in their library.
     *
     * @return void
     * @test
     */
    function a_user_can_get_the_OnHold_anime_in_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach(1, ['status' => UserLibraryStatus::OnHold]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library', [
            'status' => 'OnHold'
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Assert that there is one anime in the list
        $this->assertCount(1, $response->json()['anime']);
    }

    /**
     * Test if a user cannot get the anime in their library with an invalid status.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_the_anime_in_their_library_with_an_invalid_status()
    {
        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library', [
            'status' => 'Invalid Status'
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user cannot get the anime of another user's library.
     *
     * @return void
     * @test
     */
    function a_user_cannot_get_the_anime_of_another_users_library()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $anotherUser->id . '/library', [
            'status' => 'Watching'
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can add anime to their library (Watching).
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_library_with_status_Watching()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($this->user->id, $anime->id, 'Watching');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Watching list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Watching)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Test if a user can add anime to their library (Dropped).
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_library_with_status_Dropped()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($this->user->id, $anime->id, 'Dropped');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Dropped list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Dropped)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Test if a user can add anime to their library (Planning).
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_library_with_status_Planning()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($this->user->id, $anime->id, 'Planning');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Planning list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Planning)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Test if a user can add anime to their library (Completed).
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_library_with_status_Completed()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($this->user->id, $anime->id, 'Completed');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their Completed list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::Completed)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Test if a user can add anime to their library (OnHold).
     *
     * @return void
     * @test
     */
    function a_user_can_add_anime_to_their_library_with_status_OnHold()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($this->user->id, $anime->id, 'OnHold');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has 1 anime in their OnHold list
        $count = $this->user->library()
            ->wherePivot('status', UserLibraryStatus::OnHold)
            ->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Test if a user cannot add anime to their library with an invalid status.
     *
     * @return void
     * @test
     */
    function a_user_cannot_add_anime_to_their_library_with_an_invalid_status()
    {
        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($this->user->id, $anime->id, 'Invalid Status');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user cannot add anime to another user's library.
     *
     * @return void
     * @test
     */
    function a_user_cannot_add_anime_to_another_users_library()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Send request to add first anime to library
        $anime = Anime::first();

        $response = $this->addAnimeToLibraryAPIRequest($anotherUser->id, $anime->id, 'Watching');

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user can delete anime from their library.
     *
     * @return void
     * @test
     */
    function a_user_can_delete_anime_from_their_library()
    {
        // Add an anime to the list
        $this->user->library()->attach(1, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/library/delete', [
            'anime_id' => 1
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check that the user now has no anime in their library
        $count = $this->user->library()->count();

        $this->assertEquals(0, $count);
    }

    /**
     * Test if a user cannot delete anime from another user's library.
     *
     * @return void
     * @test
     */
    function a_user_cannot_delete_anime_from_another_users_library()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Add an anime to the list
        $anotherUser->library()->attach(1, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/library/delete', [
            'anime_id' => 1
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check that the user still has the anime in their library
        $count = $anotherUser->library()->count();

        $this->assertEquals(1, $count);
    }

    /**
     * Test if a user can search in own library.
     *
     * @return void
     * @test
     */
    function a_user_can_search_in_own_library()
    {
        // Add an anime to the user's list
        $shows = factory(Anime::class, 20)->create();

        /** @var Anime $show */
        foreach ($shows as $show)
            $this->user->library()->attach($show->id, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/library/search', [
            'query' => $shows->first()->title
        ]);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the result count is greater than zero
        $this->assertGreaterThan(0, count($response->json('results')));
    }

    /**
     * Test if a user cannot search in another user's library.
     *
     * @return void
     * @test
     */
    function a_user_cannot_search_in_another_users_library()
    {
        // Add an anime to another user's list
        $shows = factory(Anime::class, 20)->create();

        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        /** @var Anime $show */
        foreach ($shows as $show)
            $anotherUser->library()->attach($show->id, ['status' => UserLibraryStatus::Watching]);

        // Send the request
        $response = $this->auth()->json('GET', '/api/v1/users/' . $anotherUser->id . '/library/search', [
            'query' => $shows->first()->title
        ]);

        // Check whether the response was unsuccessful
        $response->assertUnsuccessfulAPIResponse();
    }

    /**
     * Sends an API request to add anime to a user's library.
     *
     * @param $userID
     * @param $animeID
     * @param $status
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    private function addAnimeToLibraryAPIRequest($userID, $animeID, $status) {
        return $this->auth()->json('POST', '/api/v1/users/' . $userID . '/library', [
            'anime_id'  => $animeID,
            'status'    => $status
        ]);
    }
}
