<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Set up the test.
     *
     * @return void
     */
    function setUp(): void
    {
        parent::setUp();

        // API response macro's
        TestResponse::macro('assertSuccessfulAPIResponse', function() {
            $this->assertSuccessful()
                ->assertJson([
                    'success' => true
                ]);
        });

        TestResponse::macro('assertUnsuccessfulAPIResponse', function() {
            $this->assertStatus(400)
                ->assertJson([
                    'success' => false
                ]);
        });
    }
}
