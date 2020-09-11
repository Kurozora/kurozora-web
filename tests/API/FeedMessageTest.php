<?php

namespace Tests\API;

use App\FeedMessage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class FeedMessageTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    public function setUp(): void
    {
        parent::setUp();

        // seed the database
        $this->artisan('love:reaction-type-add --name=Heart --mass=1');
    }

    /**
     * User can post a normal message to the feed.
     *
     * @return void
     * @test
     */
    function user_can_post_a_normal_message_to_the_feed()
    {
        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * User can post an NSFW message to the feed.
     *
     * @return void
     * @test
     */
    function user_can_post_an_nsfw_message_to_the_feed()
    {
        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'is_nsfw'       => 1,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * User can post a spoiler message to the feed.
     *
     * @return void
     * @test
     */
    function user_can_post_a_spoiler_message_to_the_feed()
    {
        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'is_nsfw'       => 0,
            'is_spoiler'    => 1,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * User can post an NSFW and spoiler message to the feed.
     *
     * @return void
     * @test
     */
    function user_can_post_an_nsfw_and_spoiler_message_to_the_feed()
    {
        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'is_nsfw'       => 1,
            'is_spoiler'    => 1,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * User can reply to a feed message.
     *
     * @return void
     * @test
     */
    function user_can_reply_to_a_feed_message()
    {
        $parent = factory(FeedMessage::class)->create();

        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'parent_id'     => $parent->id,
            'is_reply'      => 1,
            'is_reshare'    => 0,
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());
    }

    /**
     * User can re-share a feed message once.
     *
     * @return void
     * @test
     */
    function user_can_re_share_a_feed_message_once()
    {
        $parent = factory(FeedMessage::class)->create();

        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'parent_id'     => $parent->id,
            'is_reply'      => 0,
            'is_reshare'    => 1,
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feedMessages()->count());

        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'parent_id'     => $parent->id,
            'is_reply'      => 0,
            'is_reshare'    => 1,
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can reply to feed messages.
     *
     * @return void
     * @test
     */
    function user_can_reply_to_feed_messages()
    {
        $parent = factory(FeedMessage::class)->create([
            'parent_feed_message_id' => factory(FeedMessage::class)->create()->id
        ]);

        $this->auth()->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'parent_id'     => $parent->id,
            'is_reply'      => 1,
            'is_reshare'    => 0,
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();
    }

    /**
     * User cannot post to the feed if not logged in.
     *
     * @return void
     * @test
     */
    function user_cannot_post_to_the_feed_if_not_logged_in()
    {
        $this->json('POST', '/api/v1/feed', [
            'body'          => 'Hello, Kurozora!',
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * User cannot post to the feed if not logged in.
     *
     * @return void
     * @test
     */
    function feed_messages_can_have_a_maximum_length_of_240_characters()
    {
        $this->auth()->json('POST', '/api/v1/feed', [
            'body' => Str::random(240),
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        $this->auth()->json('POST', '/api/v1/feed', [
            'body' => Str::random(241),
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertUnsuccessfulAPIResponse();
    }
}
