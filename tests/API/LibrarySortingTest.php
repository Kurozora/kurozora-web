<?php

namespace Tests\API;

use App\Models\Anime;
use App\Models\AnimeRating;
use App\Enums\UserLibraryStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class LibrarySortingTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * User can sort their library based on title.
     *
     * @return void
     * @test
     */
    function user_can_sort_their_library_based_on_title()
    {
        $this->addTestingAnimeToLibrary($this->user);

        // Send the request and sort by title ascending
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'title(asc)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by title descending
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'title(desc)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * User can sort their library based on age.
     *
     * @return void
     * @test
     */
    function user_can_sort_their_library_based_on_age()
    {
        $this->addTestingAnimeToLibrary($this->user);

        // Send the request and sort by age newest
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'age(newest)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by age oldest
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'age(oldest)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * User can sort their library based on rating.
     *
     * @return void
     * @test
     */
    function user_can_sort_their_library_based_on_rating()
    {
        $this->addTestingAnimeToLibrary($this->user);

        // Send the request and sort by rating best
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'rating(best)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by rating worst
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'rating(worst)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * User can sort their library based on their own giving rating.
     *
     * @return void
     * @test
     */
    function user_can_sort_their_library_based_on_their_own_given_rating()
    {
        $this->addTestingAnimeToLibrary($this->user);

        // Send the request and sort by my rating best
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'my-rating(best)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);

        // Send the request and sort by my rating worst
        $response = $this->auth()->json('GET', '/api/v1/me/library', [
            'status'    => 'Watching',
            'sort'      => 'my-rating(worst)'
        ]);

        $this->assertMatchesJsonSnapshot($response->json()['data']);
    }

    /**
     * Adds the testing Anime to the user's library.
     *
     * @param User $user
     */
    private function addTestingAnimeToLibrary(User $user)
    {
        // Add the first Anime
        $anime = Anime::factory()->create([
            'title'             => 'Awesome Show',
            'synopsis'          => 'A very awesome show.',
            'created_at'        => now(),
            'average_rating'    => 2.5
        ]);
        $user->library()->attach($anime->id, ['status' => UserLibraryStatus::Watching]);

        AnimeRating::create([
            'anime_id'  => $anime->id,
            'user_id'   => $user->id,
            'rating'    => 3.0
        ]);

        // Add the second Anime
        $anime = Anime::factory()->create([
            'title'             => 'Be a good person!',
            'synopsis'          => 'A story about being a good person.',
            'created_at'        => now()->subDay(),
            'average_rating'    => 4.0
        ]);
        $user->library()->attach($anime->id, ['status' => UserLibraryStatus::Watching]);

        AnimeRating::create([
            'anime_id'  => $anime->id,
            'user_id'   => $user->id,
            'rating'    => 1.2
        ]);
    }
}
