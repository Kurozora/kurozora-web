<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExplorePageAPITest extends TestCase
{
    /**
     * Tests if the explore page endpoint is successful.
     *
     * @return void
     */
    function test_if_explore_page_endpoint_gives_successful_json_response()
    {
        $response = $this->json('GET', '/api/v1/explore');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true
            ]);
    }
}
