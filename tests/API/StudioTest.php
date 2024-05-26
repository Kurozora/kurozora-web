<?php

namespace Tests\API;

use App\Models\MediaStudio;
use App\Models\Studio;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StudioTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * The object containing the studio data.
     *
     * @var Studio $studio
     */
    protected Studio $studio;

    public function setUp(): void
    {
        parent::setUp();

        $this->studio = Studio::factory()->create();
    }

    /**
     * A user can view specific studio details.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_studio_details(): void
    {
        $response = $this->get('v1/studios/'.$this->studio->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the studio id in the response is the desired studio's id
        $this->assertEquals($this->studio->id, $response->json()['data'][0]['id']);
    }

    /**
     * A user can view specific studio details including relationships.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_studio_details_including_relationships(): void
    {
        $response = $this->get('v1/studios/'.$this->studio->id.'?include=shows');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the studio id in the response is the desired studio's id
        $this->assertEquals($this->studio->id, $response->json()['data'][0]['id']);

        // Check whether the response includes shows relationship
        $this->assertArrayHasKey('shows', $response->json()['data'][0]['relationships']);
    }


    /**
     * A user can view specific studio anime.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_studio_anime(): void
    {
        // Prepare studio anime
        MediaStudio::factory(25)->create([
            'studio_id' => $this->studio->id
        ]);

        $response = $this->get('v1/studios/'.$this->studio->id.'/anime');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the anime are in the response
        $this->assertNotEmpty($response->json()['data']);
    }
}
