<?php

namespace Tests\API;

use App\Enums\UserLibraryStatus;
use App\Models\Anime;
use App\Models\Episode;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class MediaRatingTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * User cannot rate anime if not in library.
     *
     * @return void
     */
    #[Test]
    function user_cannot_rate_anime_if_not_in_library(): void
    {
        // Rate the anime
        $response = $this->auth()->json('POST', 'v1/anime/' . $this->anime->id . '/rate');

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check if anime rating does not exist
        $this->assertTrue($this->user->animeRatings()->count() === 0);
    }

    /**
     * User can rate anime if in library.
     *
     * @return void
     */
    #[Test]
    function user_can_rate_anime_if_in_library(): void
    {
        // Add anime to library
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Rate the anime
        $response = $this->auth()->json('POST', 'v1/anime/' . $this->anime->id . '/rate', [
            'rating' => 2.5
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check if anime rating exists
        $this->assertTrue($this->user->animeRatings()->count() === 1);
    }

    /**
     * User can remove anime rating.
     *
     * @return void
     */
    #[Test]
    function user_can_remove_anime_rating(): void
    {
        // Add anime to library
        $this->user->track($this->anime, UserLibraryStatus::InProgress());

        // Rate the anime
        $this->user->animeRatings()->create([
            'model_type' => Anime::class,
            'model_id' => $this->anime->id,
            'rating' => 2.5,
        ]);

        // Remove the anime rating
        $response = $this->auth()->json('POST', 'v1/anime/' . $this->anime->id . '/rate', [
            'rating' => 0.0
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check if anime rating exists
        $this->assertTrue($this->user->animeRatings()->count() === 0);
    }

    /**
     * User cannot rate episode if not watched.
     *
     * @return void
     */
    #[Test]
    function user_cannot_rate_episode_if_not_watched(): void
    {
        // Get the episode
        $episode = $this->anime->episodes()->first();

        // Rate episode
        $response = $this->auth()->json('POST', 'v1/episodes/' . $episode->id . '/rate');

        // Check whether the request was unsuccessful
        $response->assertUnsuccessfulAPIResponse();

        // Check if episode rating does not exist
        $this->assertTrue($this->user->episodeRatings()->count() === 0);
    }

    /**
     * User can rate episode if watched.
     *
     * @return void
     */
    #[Test]
    function user_can_rate_episode_if_watched(): void
    {
        // Get the episode
        $episode = $this->anime->episodes()->first();

        // Mark episode as watched
        $this->user->episodes()->attach($episode);

        // Rate the episode
        $response = $this->auth()->json('POST', 'v1/episodes/' . $episode->id . '/rate', [
            'rating' => 2.5
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check if episode rating exists
        $this->assertTrue($this->user->episodeRatings()->count() === 1);
    }

    /**
     * User can remove episode rating.
     *
     * @return void
     */
    #[Test]
    function user_can_remove_episode_rating(): void
    {
        // Get the episode
        $episode = $this->anime->episodes()->first();

        // Add anime to library
        $this->user->episodes()->attach($episode);

        // Rate the anime
        $this->user->episodeRatings()->create([
            'model_type' => Episode::class,
            'model_id' => $episode->id,
            'rating' => 2.5,
        ]);

        // Remove the anime rating
        $response = $this->auth()->json('POST', 'v1/episodes/' . $episode->id . '/rate', [
            'rating' => 0.0
        ]);

        // Check whether the request was successful
        $response->assertSuccessfulAPIResponse();

        // Check if anime rating exists
        $this->assertTrue($this->user->episodeRatings()->count() === 0);
    }
}
