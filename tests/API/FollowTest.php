<?php

namespace Tests\API;

use App\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\API\Traits\ProvidesTestUser;
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
}
