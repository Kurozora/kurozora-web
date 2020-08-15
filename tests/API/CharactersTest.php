<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestAnime;

class CharactersTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestAnime;

    /**
     * A user can view all characters.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_all_characters()
    {
        $response = $this->json('GET', '/api/v1/characters', []);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the characters array is not empty
        $this->assertTrue(count($response->json()['data']) > 0);
    }

    /**
     * A user can view specific character details.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_specific_character_details()
    {
        $response = $this->get('/api/v1/characters/'.$this->character->id);

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the character id in the response is the desired character's id
        $this->assertEquals($this->character->id, $response->json()['data'][0]['id']);
    }

    /**
     * A user can view specific character details including relationships.
     *
     * @return void
     * @test
     */
    public function a_user_can_view_specific_character_details_including_relationships()
    {
        $response = $this->get('/api/v1/characters/'.$this->character->id . '?include=shows,actors');

        // Check whether the response was successful
        $response->assertSuccessfulAPIResponse();

        // Check whether the character id in the response is the desired character's id
        $this->assertEquals($this->character->id, $response->json()['data'][0]['id']);

        // Check whether the response includes shows relationship
        $this->assertArrayHasKey('shows', $response->json()['data'][0]['relationships']);

        // Check whether the response includes actors relationship
        $this->assertArrayHasKey('actors', $response->json()['data'][0]['relationships']);
    }
}
