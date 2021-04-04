<?php

namespace Tests\Unit;

use App\Models\Session;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class APNTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /** @test */
    function routeNotificationForApn_returns_the_device_tokens()
    {
        // Create some sessions for the user
        /** @var Session[] $sessions */
        $sessions = Session::factory(5)->create(['user_id' => $this->user->id]);

        // Also create a session without device token
        $sessions[] = Session::factory()->create([
            'user_id' => $this->user->id,
            'apn_device_token' => null
        ]);

        // Create the expected return value
        $expectedArray = [];

        foreach($sessions as $session) {
            if($session->apn_device_token !== null)
                $expectedArray[] = $session->apn_device_token;
        }

        // Check whether the routeNotificationForApn method returns the correct value
        $this->assertSame($expectedArray, $this->user->routeNotificationForApn());
    }
}
