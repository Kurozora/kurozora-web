<?php

namespace Tests\API;

use App\Enums\UserLibraryStatus;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class EpisodeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestAnime, ProvidesTestUser;

    /**
     * Test if an episode cannot be watched if anime not in library.
     *
     * @return void
     * @test
     */
    function an_episode_can_not_be_watched_if_anime_not_in_library()
    {
        $response = $this->auth()->json('POST', '/api/v1/anime-episodes/' . $this->episode->id . '/watched', [
            'watched' => 1
        ]);

        // Check whether the request was successful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the episode status is watched. i.e. exists == true
        $this->assertEquals(false, $this->user->watchedAnimeEpisodes()->where('episode_id', $this->episode->id)->exists());
    }

    /**
     * Test if an episode cannot be unwatched if anime is not in library.
     *
     * @return void
     * @test
     */
    function an_episode_can_not_be_unwatched_if_anime_not_in_library()
    {
        $response = $this->auth()->json('POST', '/api/v1/anime-episodes/' . $this->episode->id . '/watched', [
            'watched' => -1
        ]);

        // Check whether the request was successful
        $response->assertUnsuccessfulAPIResponse();

        // Check whether the episode status is not watched. i.e exists == false
        $this->assertEquals(false, $this->user->watchedAnimeEpisodes()->where('episode_id', $this->episode->id)->exists());
    }

    /**
     * Test if an episode can be watched if anime in library.
     *
     * @return void
     * @test
     */
    function an_episode_can_be_watched_if_anime_in_library()
    {
        // Add anime to library.
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/library', [
            'anime_id' => $this->anime->id,
            'status' => UserLibraryStatus::getDescription(UserLibraryStatus::Watching)
        ]);

        $response->assertSuccessfulAPIResponse();

        // Mark episode as watched.
        $response = $this->auth()->json('POST', '/api/v1/anime-episodes/' . $this->episode->id . '/watched', [
            'watched' => 1
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the episode status is watched. i.e. exists == true
        $this->assertEquals(true, $this->user->watchedAnimeEpisodes()->where('episode_id', $this->episode->id)->exists());
    }

    /**
     * Test if an episode can be unwatched if anime is in library.
     *
     * @return void
     * @test
     */
    function an_episode_can_be_unwatched_if_anime_in_library()
    {
        // Add anime to library.
        $response = $this->auth()->json('POST', '/api/v1/users/' . $this->user->id . '/library', [
            'anime_id' => $this->anime->id,
            'status' => UserLibraryStatus::getDescription(UserLibraryStatus::Watching)
        ]);

        $response->assertSuccessfulAPIResponse();

        // Mark episode as unwatched.
        $response = $this->auth()->json('POST', '/api/v1/anime-episodes/' . $this->episode->id . '/watched', [
            'watched' => -1
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the episode status is not watched. i.e exists == false
        $this->assertEquals(false, $this->user->watchedAnimeEpisodes()->where('episode_id', $this->episode->id)->exists());
    }
}
