<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
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
     */
    #[Test]
    public function a_user_can_view_the_cast_of_a_manga(): void
     {
        $response = $this->getJson(route('api.manga.cast', $this->manga->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the cast array is not empty
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view the related manga of a manga.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_the_related_manga_of_a_manga(): void
    {
        $response = $this->getJson(route('api.manga.related-literatures', $this->manga->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the related array is not empty
        $this->assertNotEmpty($response->json()['data'][0]);
    }

    /**
     * An authenticated user can view the related manga of a manga with personal information.
     *
     * @return void
     */
    #[Test]
    public function an_authenticated_user_can_view_the_related_manga_of_a_manga_with_personal_information(): void
    {
        $response = $this->auth()->getJson(route('api.manga.related-literatures', $this->manga->id));

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the library array is not empty
        $this->assertArrayHasKey('status', $response->json()['data'][0]['literature']['attributes']['library']);
    }

    /**
     * Manga has media stat on create.
     *
     * @return void
     */
    #[Test]
    public function manga_has_media_stat_on_create(): void
    {
        $this->assertNotNull($this->manga->mediaStat);
    }
}
