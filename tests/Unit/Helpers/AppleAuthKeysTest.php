<?php

namespace Tests\Unit\Helpers;

use App\Helpers\AppleAuthKeys;
use Carbon\Carbon;
use Tests\TestCase;

class AppleAuthKeysTest extends TestCase
{
    /** @test */
    function it_can_get_apple_auth_keys()
    {
        $keys = AppleAuthKeys::get();

        $this->assertNotEmpty($keys);
    }

    /** @test */
    function it_caches_the_keys_for_24_hours()
    {
        // To start off, the keys should not be cached
        Carbon::setTestNow();
        $this->assertFalse(AppleAuthKeys::areCached());

        // Get the keys once
        AppleAuthKeys::get();

        // The keys should be cached
        $this->assertTrue(AppleAuthKeys::areCached());

        // Exactly 24 hours later, the keys should still be cached
        Carbon::setTestNow(now()->addDay());
        $this->assertTrue(AppleAuthKeys::areCached());

        // A minute later, the keys should no longer be cached
        Carbon::setTestNow(now()->addMinute());
        $this->assertFalse(AppleAuthKeys::areCached());
    }
}
