<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;

class PeopleTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestAnime;

    /**
     * A user can view specific person details.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_person_details(): void
    {
        $response = $this->get('v1/people/'.$this->person->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the person id in the response is the desired person's id
        $this->assertEquals($this->person->id, $response->json()['data'][0]['id']);
    }

    /**
     * A user can view specific character details including relationships.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_character_details_including_relationships(): void
    {
        $response = $this->get('v1/people/'.$this->person->id . '?include=shows,characters');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the character id in the response is the desired character's id
        $this->assertEquals($this->character->id, $response->json()['data'][0]['id']);

        // Check whether the response includes shows relationship
        $this->assertArrayHasKey('shows', $response->json()['data'][0]['relationships']);

        // Check whether the response includes characters relationship
        $this->assertArrayHasKey('characters', $response->json()['data'][0]['relationships']);
    }

    /**
     * A user can view specific person characters.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_person_characters(): void
    {
        $response = $this->get('v1/people/'.$this->person->id.'/characters');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters are in the response
        $this->assertNotEmpty($response->json()['data']);
    }

    /**
     * A user can view specific person anime.
     *
     * @return void
     */
    #[Test]
    public function a_user_can_view_specific_person_anime(): void
    {
        $response = $this->get('v1/people/'.$this->person->id.'/anime');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the anime are in the response
        $this->assertNotEmpty($response->json()['data']);
    }
}
