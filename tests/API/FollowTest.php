<?php

namespace Tests\API;

use App\Http\Resources\UserResourceBasic;
use App\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\Traits\ProvidesTestUser;
use Tests\TestCase;

class FollowTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * Test if a user can follow another user.
     *
     * @return void
     * @test
     */
    function a_user_can_follow_another_user()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/follow', [
            'follow' => 1
        ])->assertSuccessfulAPIResponse();

        // Check that the user is now following one person
        $this->assertEquals($this->user->following()->count(), 1);

        // Check that the other user is now being followed by one person
        $this->assertEquals($anotherUser->followers()->count(), 1);
    }

    /**
     * Test if a user can unfollow another user.
     *
     * @return void
     * @test
     */
    function a_user_can_unfollow_another_user()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Add the other user to our following list
        $this->user->following()->attach($anotherUser);

        // Send the unfollow API request
        $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/follow', [
            'follow' => 0
        ])->assertSuccessfulAPIResponse();

        // Check that the user is now following no-one
        $this->assertEquals($this->user->following()->count(), 0);

        // Check that the other user is now being followed by no-one
        $this->assertEquals($anotherUser->followers()->count(), 0);
    }

    /**
     * Test if a user cannot follow a non-existing user.
     *
     * @return void
     * @test
     */
    function a_user_cannot_follow_an_invalid_user()
    {
        $this->auth()->json('POST', '/api/v1/users/' . 99999 . '/follow', [
            'follow' => 1
        ])->assertNotFound();

        // Check that the user is still following no-one
        $this->assertEquals($this->user->following()->count(), 0);
    }

    /**
     * Test if a user cannot follow a user they already follow.
     *
     * @return void
     * @test
     */
    function a_user_cannot_follow_a_user_they_already_follow()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Add the other user to our following list
        $this->user->following()->attach($anotherUser);

        // Send the follow API request
        $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/follow', [
            'follow' => 1
        ])->assertUnsuccessfulAPIResponse();

        // Check that the user is now following one person
        $this->assertEquals($this->user->following()->count(), 1);

        // Check that the other user is now being followed by one person
        $this->assertEquals($anotherUser->followers()->count(), 1);
    }

    /**
     * Test if a user cannot unfollow a user they do not follow.
     *
     * @return void
     * @test
     */
    function a_user_cannot_unfollow_a_user_they_do_not_follow()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        // Send the unfollow API request
        $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/follow', [
            'follow' => 0
        ])->assertUnsuccessfulAPIResponse();

        // Check that the user is now following one person
        $this->assertEquals($this->user->following()->count(), 0);

        // Check that the other user is now being followed by one person
        $this->assertEquals($anotherUser->followers()->count(), 0);
    }

    /**
     * Test if a user can get their list of followers.
     *
     * @return void
     * @test
     */
    function a_user_can_get_their_followers_list()
    {
        // Add a follower
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->user->followers()->attach($anotherUser);

        // Request the list of followers
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/followers');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the follower
        $response->assertJson([
            'followers' => [
                UserResourceBasic::make($anotherUser)->toArray(),
            ]
        ]);
    }

    /**
     * Test if a user can get their list of following.
     *
     * @return void
     * @test
     */
    function a_user_can_get_their_following_list()
    {
        // Add a user to the following list
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->user->following()->attach($anotherUser);

        // Request the list of following
        $response = $this->auth()->json('GET', '/api/v1/users/' . $this->user->id . '/following');

        // Check that the response is successful
        $response->assertSuccessfulAPIResponse();

        // Check that the response contains the user
        $response->assertJson([
            'following' => [
                UserResourceBasic::make($anotherUser)->toArray(),
            ]
        ]);
    }

    /**
     * Test if a user receives a notification when someone follows them.
     *
     * @return void
     * @test
     */
    function a_user_receives_a_notification_when_someone_follows_them()
    {
        /** @var User $anotherUser */
        $anotherUser = factory(User::class)->create();

        $this->auth()->json('POST', '/api/v1/users/' . $anotherUser->id . '/follow', [
            'follow' => 1
        ])->assertSuccessfulAPIResponse();

        // Check that the other user now has a notification
        $this->assertEquals(1, $anotherUser->notifications()->count());
    }
}
