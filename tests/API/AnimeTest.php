<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;
use Tests\Traits\ProvidesTestUser;

class AnimeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestAnime;

    /**
     * A user can view the cast of an anime.
     *
     * @return void
     * @test
     */
     public function a_user_can_view_the_cast_of_an_anime(): void
     {
         $response = $this->getJson(route('api.anime.cast', $this->anime->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the cast array is not empty
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view the related anime of an anime.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_the_related_anime_of_an_anime(): void
    {
        $response = $this->getJson(route('api.anime.related-shows', $this->anime->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the related array is not empty
        $this->assertNotEmpty($response->json()['data'][0]);
    }

    /**
     * An authenticated user can view the related anime of an anime with personal information.
     *
     * @return void
     * @test
     */
    public function an_authenticated_user_can_view_the_related_anime_of_an_anime_with_personal_information(): void
    {
        $response = $this->auth()->getJson(route('api.anime.related-shows',$this->anime->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the current_user array is not empty
        $this->assertArrayHasKey('status', $response->json()['data'][0]['show']['attributes']['library']);
    }

    /**
     * Anime has media stat on create.
     *
     * @return void
     * @test
     */
    public function anime_has_media_stat_on_create(): void
    {
        $this->assertNotNull($this->anime->mediaStat);
    }
}
