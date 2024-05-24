<?php

namespace Tests\Unit\Helpers;

use App\Helpers\AppleAuthKeys;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AppleAuthKeysTest extends TestCase
{
    /**
     * It can get apple auth keys.
     *
     * @return void
     */
    #[Test]
    public function it_can_get_apple_auth_keys(): void
    {
        $keys = AppleAuthKeys::get();

        $this->assertNotEmpty($keys);
    }

    /**
     * It caches the keys for 24 hours.
     *
     * @return void
     */
    #[Test]
    public function it_caches_the_keys_for_24_hours(): void
    {
        // To start off, the keys should not be cached
        Carbon::setTestNow();
        $this->assertFalse(AppleAuthKeys::areCached());

        // Get the keys once
        AppleAuthKeys::get();

        // The keys should be cached
        $this->assertTrue(AppleAuthKeys::areCached());

        // About 24 hours later, the keys should still be cached
        Carbon::setTestNow(now()->addDay()->subMinute());
        $this->assertTrue(AppleAuthKeys::areCached());

        // 24 hours and a minute later, the keys should no longer be cached
        Carbon::setTestNow(now()->addMinute());
        $this->assertFalse(AppleAuthKeys::areCached());
    }
}
