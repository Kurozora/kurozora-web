<?php

namespace Tests\API;

use App\Models\FeedMessage;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

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
     */
    #[Test]
    function user_can_post_a_normal_message_to_the_feed(): void
    {
        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->count());
    }

    /**
     * User can post an NSFW message to the feed.
     *
     * @return void
     */
    #[Test]
    function user_can_post_an_nsfw_message_to_the_feed(): void
    {
        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'is_nsfw'       => 1,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->count());
    }

    /**
     * User can post a spoiler message to the feed.
     *
     * @return void
     */
    #[Test]
    function user_can_post_a_spoiler_message_to_the_feed(): void
    {
        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'is_nsfw'       => 0,
            'is_spoiler'    => 1,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->count());
    }

    /**
     * User can post an NSFW and spoiler message to the feed.
     *
     * @return void
     */
    #[Test]
    function user_can_post_an_nsfw_and_spoiler_message_to_the_feed(): void
    {
        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'is_nsfw'       => 1,
            'is_spoiler'    => 1,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->count());
    }

    /**
     * User can reply to a feed message.
     *
     * @return void
     */
    #[Test]
    function user_can_reply_to_a_feed_message(): void
    {
        $parent = FeedMessage::factory()->create();

        $response = $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'parent_id'     => $parent->id,
            'is_reply'      => 1,
            'is_reshare'    => 0,
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ]);

        $response->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->where('is_reply', 1)->count());
    }

    /**
     * User can re-share a feed message once.
     *
     * @return void
     */
    #[Test]
    function user_can_re_share_a_feed_message_once(): void
    {
        $parent = FeedMessage::factory()->create();

        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'parent_id'     => $parent->id,
            'is_reply'      => 0,
            'is_reshare'    => 1,
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->where('is_reshare', 1)->count());

        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
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
     */
    #[Test]
    function user_can_reply_to_feed_messages(): void
    {
        $parent = FeedMessage::factory()->create([
            'parent_feed_message_id' => FeedMessage::factory()->create()->id
        ]);

        $this->auth()->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
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
     */
    #[Test]
    function user_cannot_post_to_the_feed_if_not_logged_in(): void
    {
        $this->json('POST', 'v1/feed', [
            'content'       => 'Hello, Kurozora!',
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * Feed messages cannot cross maximum content length.
     *
     * @return void
     */
    #[Test]
    function feed_messages_cannot_cross_maximum_content_length(): void
    {
        $this->auth()->json('POST', 'v1/feed', [
            'content'       => Str::random(FeedMessage::MAX_CONTENT_LENGTH),
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertSuccessfulAPIResponse();

        $this->auth()->json('POST', 'v1/feed', [
            'content'       => Str::random(FeedMessage::MAX_CONTENT_LENGTH + 1),
            'is_nsfw'       => 0,
            'is_spoiler'    => 0,
        ])->assertUnsuccessfulAPIResponse();
    }
}
