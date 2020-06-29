<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;

class ActorsTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestAnime;

    /**
     * A user can view all actors.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_all_actors()
    {
        $response = $this->json('GET', '/api/v1/actors', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the actors array is not empty
        $this->assertTrue(count($response->json()['actors']) > 0);
    }

    /**
     * A user can view specific actor details.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_specific_actor_details()
    {
        $response = $this->get('/api/v1/actors/'.$this->actor->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the actor id in the response is the desired actor's id
        $this->assertEquals($response->json()['actor']['id'], $this->actor->id);
    }
}
