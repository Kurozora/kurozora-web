<?php

namespace Tests\API;

use App\FeedMessage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class PostToFeedTest extends TestCase
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
        $this->auth()->json('POST', '/api/v1/feed', [
            'body' => 'Hello, Kurozora!'
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * Test if a user can post to the feed as a reply.
     *
     * @return void
     * @test
     */
    function a_user_can_post_to_the_feed_as_a_reply()
    {
        $parent = factory(FeedMessage::class)->create();

        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'in_reply_to'   => $parent->id
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * Test if a user can only reply to top level feed messages.
     *
     * @return void
     * @test
     */
    function a_user_can_only_reply_to_top_level_feed_messages()
    {
        $parent = factory(FeedMessage::class)->create([
            'parent_feed_message_id' => factory(FeedMessage::class)->create()->id
        ]);

        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'in_reply_to'   => $parent->id
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user cannot post to the feed if not logged in.
     *
     * @return void
     * @test
     */
    function a_user_cannot_post_to_the_feed_if_not_logged_in()
    {
        $this->json('POST', '/api/v1/feed', [
            'body' => 'Hello, Kurozora!'
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * Test if a user cannot post to the feed if not logged in.
     *
     * @return void
     * @test
     */
    function feed_messages_can_have_a_maximum_length_of_240_characters()
    {
        $this->auth()->json('POST', '/api/v1/feed', [
            'body' => Str::random(240)
        ])->assertSuccessfulAPIResponse();

        $this->auth()->json('POST', '/api/v1/feed', [
            'body' => Str::random(241)
        ])->assertUnsuccessfulAPIResponse();
    }
}
