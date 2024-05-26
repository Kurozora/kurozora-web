<?php

namespace Tests\API;

use App\Enums\UserLibraryStatus;
use App\Models\Episode;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class EpisodeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestAnime, ProvidesTestUser;

    /**
     * A user can view specific episode details.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_episode_details(): void
    {
        /** @var Episode $episode */
        $episode = Episode::factory()->create();

        $response = $this->get('v1/episodes/'.$episode->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the episode id in the response is the desired episode's id
        $this->assertEquals($episode->id, $response->json()['data'][0]['id']);
    }

    /**
     * An authenticated user can view specific episode details.
     *
     * @return void
     */
    #[Test]
    public function an_authenticated_user_can_view_specific_episode_details(): void
    {
        /** @var Episode $episode */
        $episode = Episode::factory()->create();

        $response = $this->auth()->json('GET', 'v1/episodes/'.$episode->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the current_user array is not empty
        $this->assertArrayHasKey('isWatched', $response->json()['data'][0]['attributes']);
    }

    /**
     * Test if an episode cannot be watched if anime not in library.
     *
     * @return void
     */
    #[Test]
    function an_episode_can_not_be_watched_if_anime_not_in_library(): void
    {
        $response = $this->auth()->json('POST', 'v1/episodes/' . $this->episode->id . '/watched');

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the episode is not watched
        $this->assertEpisodeWatched(false, $this->episode);
    }

    /**
     * Test if an episode cannot be unwatched if anime is not in library.
     *
     * @return void
     */
    #[Test]
    function an_episode_can_not_be_unwatched_if_anime_not_in_library(): void
    {
        $response = $this->auth()->json('POST', 'v1/episodes/' . $this->episode->id . '/watched');

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the episode is not watched
        $this->assertEpisodeWatched(false, $this->episode);
    }

    /**
     * Test if an episode can be watched if anime in library.
     *
     * @return void
     */
    #[Test]
    function an_episode_can_be_watched_if_anime_in_library(): void
    {
        // Add the Anime to the library
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Mark episode as watched
        $response = $this->auth()->json('POST', 'v1/episodes/' . $this->episode->id . '/watched');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the episode is watched
        $this->assertEpisodeWatched(true, $this->episode);
    }

    /**
     * Test if an episode can be unwatched if anime is in library.
     *
     * @return void
     */
    #[Test]
    function an_episode_can_be_unwatched_if_anime_in_library(): void
    {
        // Add the Anime to the library and mark episode as watched
        $this->user->track($this->anime, UserLibraryStatus::InProgress());
        $this->auth()->json('POST', 'v1/episodes/' . $this->episode->id . '/watched');

        // Mark episode as unwatched.
        $response = $this->auth()->json('POST', 'v1/episodes/' . $this->episode->id . '/watched');

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the episode is watched
        $this->assertEpisodeWatched(false, $this->episode);
    }

    /**
     * Asserts whether the given episode is watched by the user or not.
     *
     * @param bool $expected
     * @param Episode $episode
     */
    private function assertEpisodeWatched(bool $expected, Episode $episode)
    {
        $exists = $this->user->episodes()->where('episode_id', $episode->id)->exists();

        $message = 'Episode ID ' . $episode->id .' was ' . ($exists ? 'found' : 'not found') . ' in the user’s watch list.';

        $this->assertEquals($expected, $exists, $message);
    }
}
