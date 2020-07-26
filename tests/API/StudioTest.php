<?php

namespace Tests\API;

use App\Studio;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class StudioTest extends TestCase
{
    use DatabaseMigrations;

    /** @var Studio $studio */
    protected $studio;

    public function setUp(): void
    {
        parent::setUp();

        $this->studio = factory(Studio::class)->create();
    }

    /**
     * A user can view all studios.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_all_studios()
    {
        $response = $this->json('GET', '/api/v1/studios', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the studios array is not empty
        $this->assertTrue(count($response->json()['data']) > 0);
    }

    /**
     * A user can view specific studio details.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_specific_studio_details()
    {
        $response = $this->get('/api/v1/studios/'.$this->studio->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the studio id in the response is the desired studio's id
        $this->assertEquals($response->json()['data'][0]['id'], $this->studio->id);
    }
}
