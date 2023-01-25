<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestManga;
use Tests\Traits\ProvidesTestUser;

class MangaTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser, ProvidesTestManga;

    /**
     * A user can view the cast of a manga.
     *
     * @return void
     * @test
     */
     public function a_user_can_view_the_cast_of_a_manga(): void
     {
        $response = $this->json('GET', 'v1/manga/' . $this->manga->id . '/cast');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the cast array is not empty
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view the related manga of a manga.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_the_related_manga_of_a_manga(): void
    {
        $response = $this->json('GET', 'v1/manga/' . $this->manga->id . '/related-manga');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the related array is not empty
        $this->assertNotEmpty($response->json()['data'][0]);
    }

    /**
     * An authenticated user can view the related manga of a manga with personal information.
     *
     * @return void
     * @test
     */
    public function an_authenticated_user_can_view_the_related_manga_of_a_manga_with_personal_information(): void
    {
        $response = $this->auth()->json('GET', 'v1/manga/' . $this->manga->id . '/related-manga');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the current_user array is not empty
        $this->assertArrayHasKey('libraryStatus', $response->json()['data'][0]['book']['attributes']);
    }
}
