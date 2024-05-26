<?php

namespace Tests\Unit;

use App\Models\SessionAttribute;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\ProvidesTestUser;

class APNTest extends TestCase
{
    use DatabaseMigrations, ProvidesTestUser;

    /**
     * routeNotificationForApn returns the device tokens.
     *
     * @return void
     */
    #[Test]
    public function routeNotificationForApn_returns_the_device_tokens(): void
    {
        // Create some sessions for the user
        /** @var SessionAttribute[] $sessionAttributes */
        $sessionAttributes = SessionAttribute::factory(5)->create();

        // Also create a session without APN device token
        $sessionAttributes[] = SessionAttribute::factory()->create([
            'apn_device_token' => null
        ]);

        // Create the expected return value
        $expectedArray = [];

        foreach ($sessionAttributes as $sessionAttribute) {
            if ($sessionAttribute->apn_device_token !== null)
                $expectedArray[] = $sessionAttribute->apn_device_token;
        }

        // Check whether the routeNotificationForApn method returns the correct value
        $this->assertSame($expectedArray, $this->user->routeNotificationForApn());
    }
}
