<?php

namespace Tests\API;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class FollowTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can follow another user.
     *
     * @return void
     */
    #[Test]
    function a_user_can_follow_another_user(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->auth()->json('POST', 'v1/users/' . $anotherUser->id . '/follow')->assertSuccessfulAPIResponse();

        // Check that the user is now following one person
        $this->assertEquals(1, $this->user->followedModels()->count());

        // Check that the other user is now being followed by one person
        $this->assertEquals(1, $anotherUser->followers()->count());
    }

    /**
     * Test if a user can unfollow another user.
     *
     * @return void
     */
    #[Test]
    function a_user_can_unfollow_another_user(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        // Add the other user to our following list
        $this->user->followedModels()->attach($anotherUser);

        // Send the unfollow API request
        $this->auth()->json('POST', 'v1/users/' . $anotherUser->id . '/follow')->assertSuccessfulAPIResponse();

        // Check that the user is now following no-one
        $this->assertEquals(0, $this->user->followedModels()->count());

        // Check that the other user is now being followed by no-one
        $this->assertEquals(0, $anotherUser->followers()->count());
    }

    /**
     * Test if a user cannot follow a non-existing user.
     *
     * @return void
     */
    #[Test]
    function a_user_cannot_follow_an_invalid_user(): void
    {
        $this->auth()->json('POST', 'v1/users/' . 99999 . '/follow')->assertNotFound();

        // Check that the user is still following no-one
        $this->assertEquals(0, $this->user->followedModels()->count());
    }

    /**
     * Test if a user can get their list of followers.
     *
     * @return void
     */
    #[Test]
    function a_user_can_get_someone_elses_followers_list(): void
    {
        // Add a follower
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $anotherUser->followers()->attach($this->user);

        // Request the list of followers
        $response = $this->auth()->json('GET', 'v1/users/' . $anotherUser->id . '/followers');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the follower
        $this->assertNotEmpty($response['data']);
    }

    /**
     * Test if a user can get their list of following.
     *
     * @return void
     */
    #[Test]
    function a_user_can_get_someone_elses_following_list(): void
    {
        // Add a user to the following list
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $anotherUser->followedModels()->attach($this->user);

        // Request the list of following
        $response = $this->auth()->json('GET', 'v1/users/' . $anotherUser->id . '/following');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the user
        $this->assertNotEmpty($response['data']);
    }

    /**
     * Test if a user receives a notification when someone follows them.
     *
     * @return void
     */
    #[Test]
    function a_user_receives_a_notification_when_someone_follows_them(): void
    {
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();

        $this->auth()->json('POST', 'v1/users/' . $anotherUser->id . '/follow', [
            'follow' => 1
        ])->assertSuccessfulAPIResponse();

        // Check that the other user now has a notification
        $this->assertEquals(1, $anotherUser->notifications()->count());
    }
}
