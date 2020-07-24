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
     * A user can view the related anime of an anime.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_the_related_anime_of_an_anime()
    {
        $response = $this->json('GET', '/api/v1/anime/' . $this->anime->id . '/relations', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters array is not empty
        $this->assertTrue(count($response->json()['related'][0]['show']) > 0);
    }

    /**
     * An authenticated user can view the related anime of an anime with personal information.
     *
     * @return void
     * @test
     */
    public function an_authenticated_user_can_view_the_related_anime_of_an_anime_with_personal_information()
    {
        $response = $this->auth()->json('GET', '/api/v1/anime/' . $this->anime->id . '/relations', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters array is not empty
        $this->assertTrue(count($response->json()['related'][0]['show']['current_user']) > 0);
    }
}
