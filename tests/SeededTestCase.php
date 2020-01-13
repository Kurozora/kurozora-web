<?php

namespace Tests;

use Illuminate\Support\Facades\Artisan;

class SeededTestCase extends TestCase
{
    /**
     * Set up the test.
     *
     * @return void
     */
    function setUp(): void
    {
        parent::setUp();

        // Seed the database
        Artisan::call('db:seed');
    }
}
