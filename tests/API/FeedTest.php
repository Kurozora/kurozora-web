<?php

namespace Tests\API;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\API\Traits\ProvidesTestUser;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can post to the feed.
     *
     * @return void
     * @test
     */
    function a_user_can_post_to_the_feed()
    {
        $this->json('POST', '/api/v1/feed', [
            'body' => 'Hello, Kurozora!'
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }
}
