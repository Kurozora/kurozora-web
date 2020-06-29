<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;

class AnimeTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestAnime;

    /**
     * A user can view the cast of a show.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_the_cast_of_a_show()
    {
        $response = $this->json('GET', '/api/v1/anime/' . $this->anime->id . '/cast', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters array is not empty
        $this->assertTrue(count($response->json()['cast']) > 0);
    }
}
