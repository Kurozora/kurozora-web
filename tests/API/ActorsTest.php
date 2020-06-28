<?php

namespace Tests\API;

use App\Actor;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class ActorsTest extends TestCase
{
    use DatabaseMigrations;

    /** @var Actor $actor */
    protected $actor;

    public function setUp(): void
    {
        parent::setUp();

        $this->actor = factory(Actor::class)->create();
    }

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

        // Check whether the studios array is not empty
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

        // Check whether the studio id in the response is the desired studio's id
        $this->assertEquals($response->json()['actor']['id'], $this->actor->id);
    }
}
