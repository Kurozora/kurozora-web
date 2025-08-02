<?php

namespace Tests\API;

use App\Models\FeedMessage;
use App\Models\User;
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
            'content' => 'Hello, Kurozora!',
            'is_nsfw' => 0,
            'is_spoiler' => 0,
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
            'content' => 'Hello, Kurozora!',
            'is_nsfw' => 1,
            'is_spoiler' => 0,
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
            'content' => 'Hello, Kurozora!',
            'is_nsfw' => 0,
            'is_spoiler' => 1,
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
            'content' => 'Hello, Kurozora!',
            'is_nsfw' => 1,
            'is_spoiler' => 1,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->count());
    }

    /**
     * User can update own feed message.
     *
     * @return void
     */
    #[Test]
    function user_can_update_own_feed_message(): void
    {
        $feedMessage = FeedMessage::factory()->create([
            'user_id' => $this->user->id,
            'content' => 'Initial content',
        ]);

        $this->auth()->json('POST', 'v1/feed/messages/' . $feedMessage->id . '/update', [
            'content' => 'Updated content',
            'is_nsfw' => 0,
            'is_spoiler' => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals('Updated content', $feedMessage->fresh()->content);
    }

    /**
     * User cannot update the feed message of other users.
     *
     * @return void
     */
    #[Test]
    function user_cannot_update_the_feed_message_of_other_users(): void
    {
        $anotherUser = User::factory()->create();
        $feedMessage = FeedMessage::factory()->create([
            'user_id' => $anotherUser->id,
            'content' => 'Initial content',
        ]);

        $this->auth()->json('POST', 'v1/feed/messages/' . $feedMessage->id . '/update', [
            'content' => 'Updated content',
            'is_nsfw' => 0,
            'is_spoiler' => 0,
        ])->assertUnsuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals('Initial content', $feedMessage->fresh()->content);
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
            'content' => 'Hello, Kurozora!',
            'parent_id' => $parent->id,
            'is_reply' => 1,
            'is_reshare' => 0,
            'is_nsfw' => 0,
            'is_spoiler' => 0,
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
            'content' => 'Hello, Kurozora!',
            'parent_id' => $parent->id,
            'is_reply' => 0,
            'is_reshare' => 1,
            'is_nsfw' => 0,
            'is_spoiler' => 0,
        ])->assertSuccessfulAPIResponse();

        // Check whether the feed message was created
        $this->assertEquals(1, $this->user->feed_messages()->where('is_reshare', 1)->count());

        $this->auth()->json('POST', 'v1/feed', [
            'content' => 'Hello, Kurozora!',
            'parent_id' => $parent->id,
            'is_reply' => 0,
            'is_reshare' => 1,
            'is_nsfw' => 0,
            'is_spoiler' => 0,
        ])->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can pin a feed message.
     *
     * @return void
     */
    #[Test]
    function user_can_pin_a_feed_message(): void
    {
        $feedMessage = FeedMessage::factory()->create([
            'user_id' => $this->user->id
        ]);

       $this->auth()->json('POST', 'v1/feed/messages/' . $feedMessage->id . '/pin')->assertSuccessfulAPIResponse();
    }

    /**
     * User cannot pin the feed message of another user.
     *
     * @return void
     */
    #[Test]
    function user_cannot_pin_the_feed_message_of_another_user(): void
    {
        $anotherUser = User::factory()->create();

        $feedMessage = FeedMessage::factory()->create([
            'user_id' => $anotherUser->id
        ]);

        $this->auth()->json('POST', 'v1/feed/messages/' . $feedMessage->id . '/pin')->assertUnsuccessfulAPIResponse();
    }

    /**
     * User can delete own feed message.
     *
     * @return void
     */
    #[Test]
    function user_can_delete_own_feed_message(): void
    {
        $feedMessage = FeedMessage::factory()->create([
            'user_id' => $this->user->id
        ]);

        $this->auth()->json('POST', 'v1/feed/messages/' . $feedMessage->id . '/delete')->assertSuccessfulAPIResponse();
    }

    /**
     * User cannot delete the feed message of another user.
     *
     * @return void
     */
    #[Test]
    function user_cannot_delete_the_feed_message_of_another_user(): void
    {
        $anotherUser = User::factory()->create();

        $feedMessage = FeedMessage::factory()->create([
            'user_id' => $anotherUser->id
        ]);

        $this->auth()->json('POST', 'v1/feed/messages/' . $feedMessage->id . '/delete')->assertUnsuccessfulAPIResponse();
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
            'content' => 'Hello, Kurozora!',
            'is_nsfw' => 0,
            'is_spoiler' => 0,
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
            'content' => Str::random(FeedMessage::MAX_CONTENT_LENGTH),
            'is_nsfw' => 0,
            'is_spoiler' => 0,
        ])->assertSuccessfulAPIResponse();

        $this->auth()->json('POST', 'v1/feed', [
            'content' => Str::random(FeedMessage::MAX_CONTENT_LENGTH + 1),
            'is_nsfw' => 0,
            'is_spoiler' => 0,
        ])->assertUnsuccessfulAPIResponse();
    }
}
